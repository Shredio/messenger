<?php declare(strict_types = 1);

namespace Shredio\Messenger;

use Shredio\Messenger\Bus\RoutableBus;
use Shredio\Messenger\Message\AsynchronousMessage;
use Shredio\Messenger\Message\CommandMessage;
use Shredio\Messenger\Message\ConfigureMessage;
use Shredio\Messenger\Message\EventMessage;
use Shredio\Messenger\Message\PriorityAwareMessage;
use Shredio\Messenger\Message\PublicMessage;
use Shredio\Messenger\Message\QueryMessage;
use Shredio\Messenger\Message\RoutableMessage;
use Shredio\Messenger\Priority\MessagePriority;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

final readonly class DefaultMessageDispatcher implements MessageDispatcher
{

	/**
	 * @param string[] $otherServiceNames
	 */
	public function __construct(
		private string $currentServiceName,
		private array $otherServiceNames,
		private RoutableBus $bus,
	)
	{
	}

	/**
	 * @throws ExceptionInterface
	 */
	public function dispatch(RoutableMessage $message, ?ConfigureMessage $config = null): void
	{
		$this->bus->dispatch($message, [
			new TransportNamesStamp($this->getTransportNames(
				$message,
				!$message instanceof AsynchronousMessage,
				$message instanceof PublicMessage,
				$config?->priority ?: $this->getPriority($message),
			)),
		]);
	}

	/**
	 * @return string[]
	 */
	private function getTransportNames(object $message, bool $sync, bool $public, MessagePriority $priority): array
	{
		if ($message instanceof EventMessage) {
			if (!$public) {
				if ($sync) {
					return ['sync'];
				} else {
					return [$this->getTransportName($this->currentServiceName, 'events', $priority)];
				}
			}

			$transports = [];

			if ($sync) {
				$transports[] = 'sync';
			} else {
				$transports[] = $this->getTransportName($this->currentServiceName, 'events', $priority);
			}

			foreach ($this->otherServiceNames as $serviceName) {
				$transports[] = $this->getTransportName($serviceName, 'events', $priority);
			}

			return $transports;
		}

		if ($message instanceof CommandMessage) {
			if ($sync) {
				return ['sync'];
			} else {
				return [$this->getTransportName($this->currentServiceName, 'commands', $priority)];
			}
		}

		if ($message instanceof QueryMessage) {
			if (!$sync) {
				trigger_error('Queries must be sync.', E_USER_WARNING);
			}

			return ['sync'];
		}

		return [];
	}

	private function getTransportName(string $serviceName, string $namespace, MessagePriority $priority): string
	{
		$transport = sprintf('%s_%s', $serviceName, $namespace);
		$prioritySlug = $priority->getSlug();

		if ($prioritySlug !== '') {
			return sprintf('%s_%s', $prioritySlug, $transport);
		}

		return $transport;
	}

	private function getPriority(RoutableMessage $message): MessagePriority
	{
		if ($message instanceof PriorityAwareMessage) {
			return $message->getPriority();
		}

		return MessagePriority::Normal;
	}

}
