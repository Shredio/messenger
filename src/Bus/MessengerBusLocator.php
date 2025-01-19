<?php declare(strict_types = 1);

namespace Shredio\Messenger\Bus;

use RuntimeException;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class MessengerBusLocator
{

	public function __construct(
		public ?CommandBus $commandBus = null,
		public ?QueryBus $queryBus = null,
		public ?EventBus $eventBus = null,
	)
	{
	}

	public function getCommandBus(): MessageBusInterface
	{
		return $this->commandBus ?? throw new RuntimeException('Command bus not set.');
	}

	public function getQueryBus(): MessageBusInterface
	{
		return $this->queryBus ?? throw new RuntimeException('Query bus not set.');
	}

	public function getEventBus(): MessageBusInterface
	{
		return $this->eventBus ?? throw new RuntimeException('Event bus not set.');
	}

}
