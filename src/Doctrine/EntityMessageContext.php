<?php declare(strict_types = 1);

namespace Shredio\Messenger\Doctrine;

use Doctrine\ORM\PersistentCollection;
use Shredio\Messenger\Bus\RoutableBus;

final readonly class EntityMessageContext
{

	/**
	 * @param array<string, array{mixed, mixed}|PersistentCollection<int, object>>|null $changeSet
	 */
	public function __construct(
		public object $object,
		public RoutableBus $bus,
		public DoctrineEvent $event,
		private ?array $changeSet = null,
	)
	{
	}

	public function hasChangedField(string $field): bool
	{
		return $this->changeSet === null || isset($this->changeSet[$field]);
	}

	public function isPersist(): bool
	{
		return $this->event === DoctrineEvent::Persist;
	}

	public function isUpdate(): bool
	{
		return $this->event === DoctrineEvent::Update;
	}

	public function isRemove(): bool
	{
		return $this->event === DoctrineEvent::Remove;
	}

}
