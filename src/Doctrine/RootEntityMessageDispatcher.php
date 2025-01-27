<?php declare(strict_types = 1);

namespace Shredio\Messenger\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use ReflectionClass;
use RuntimeException;
use Shredio\Messenger\Doctrine\Attribute\EntityMessageDispatcher as EntityMessageDispatcherAttribute;
use Shredio\Messenger\LateMessageDispatcher;
use Shredio\Messenger\MessageDispatcher;

final class RootEntityMessageDispatcher implements EventSubscriber
{

	private ?LateMessageDispatcher $lateMessageDispatcher = null;

	/** @var array<class-string, list<EntityMessageDispatcher>> */
	private array $dispatchersCache = [];

	/**
	 * @param EntityMessageDispatcher[] $dispatchers
	 */
	public function __construct(
		private readonly array $dispatchers,
		private MessageDispatcher $messageDispatcher,
	)
	{
	}

	public function getSubscribedEvents(): array
	{
		return [
			Events::postPersist,
			Events::postFlush,
			Events::onFlush,
		];
	}

	public function postPersist(PostPersistEventArgs $event): void
	{
		$this->tryToDispatch(
			$event->getObject(),
			$event->getObjectManager(),
			$this->lateMessageDispatcher ?? throw new RuntimeException('Unexpected error, late message dispatcher is not set'),
			DoctrineEvent::Persist,
		);
	}

	public function onFlush(OnFlushEventArgs $event): void
	{
		$this->lateMessageDispatcher = $dispatcher = new LateMessageDispatcher($this->messageDispatcher);

		$em = $event->getObjectManager();
		$uow = $em->getUnitOfWork();

		// Insertions are in another event, because we need to know the ID
		// onFlush is called before inserting entities to the database

		foreach ($uow->getScheduledEntityUpdates() as $entity) {
			$this->tryToDispatch($entity, $em, $dispatcher, DoctrineEvent::Update, true);
		}

		foreach ($uow->getScheduledEntityDeletions() as $entity) {
			$this->tryToDispatch($entity, $em, $dispatcher, DoctrineEvent::Remove);
		}

		// collections
		foreach ($uow->getScheduledCollectionUpdates() as $collection) {
			foreach ($collection as $entity) {
				$this->tryToDispatch($entity, $em, $dispatcher, DoctrineEvent::Update, true);
			}
		}

		foreach ($uow->getScheduledCollectionDeletions() as $collection) {
			// Deferred explicit tracked collections can be removed only when owning relation was persisted
			$owner = $collection->getOwner();

			if (!$owner) {
				continue;
			}

			if ($em->getClassMetadata($owner::class)->isChangeTrackingDeferredImplicit() || $uow->isScheduledForDirtyCheck($owner)) {
				foreach ($collection as $entity) {
					$this->tryToDispatch($entity, $em, $dispatcher, DoctrineEvent::Remove);
				}
			}
		}
	}

	private function tryToDispatch(
		object $entity,
		EntityManagerInterface $em,
		MessageDispatcher $dispatcher,
		DoctrineEvent $event,
		bool $includeChangeSet = false,
	): void
	{
		$dispatchers = $this->dispatchersCache[$entity::class] ??= $this->getDispatchers($entity, $em);

		if (!$dispatchers) {
			return;
		}

		$context = new EntityMessageContext(
			$entity,
			$dispatcher,
			$event,
			$includeChangeSet ? $em->getUnitOfWork()->getEntityChangeSet($entity) : null,
		);

		foreach ($dispatchers as $dispatcher) {
			$dispatcher->dispatchMessages($context);
		}
	}

	public function postFlush(): void
	{
		$this->lateMessageDispatcher?->execute();
		$this->lateMessageDispatcher = null;
	}

	/**
	 * @return list<EntityMessageDispatcher>
	 */
	private function getDispatchers(object $object, EntityManagerInterface $em): array
	{
		if (!isset($this->dispatchersCache[$object::class])) {
			$dispatchers = [];

			foreach ($this->dispatchers as $dispatcher) {
				if ($dispatcher->supports($object)) {
					$dispatchers[] = $dispatcher;
				}
			}

			/** @var class-string $originalClass */
			$originalClass = $em->getClassMetadata($object::class)->getName();

			foreach ($this->getDispatchersOnClass($originalClass) as $dispatcher) {
				if ($dispatcher->supports($object)) {
					$dispatchers[] = $dispatcher;
				}
			}

			$this->dispatchersCache[$object::class] = $dispatchers;

			if ($originalClass !== $object::class) { // object can be a proxy
				$this->dispatchersCache[$originalClass] = $dispatchers;
			}
		}

		return $this->dispatchersCache[$object::class];
	}

	/**
	 * @param class-string $className
	 * @return EntityMessageDispatcher[]
	 */
	private function getDispatchersOnClass(string $className): iterable
	{
		$reflection = new ReflectionClass($className);

		foreach ($reflection->getAttributes(EntityMessageDispatcherAttribute::class) as $reflectionAttribute) {
			/** @var EntityMessageDispatcherAttribute $attribute */
			$attribute = $reflectionAttribute->newInstance();

			yield new $attribute->dispatcher();
		}
	}

}
