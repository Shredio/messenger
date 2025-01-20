<?php declare(strict_types = 1);

namespace Shredio\Messenger\Bus;

use RuntimeException;

final readonly class DefaultMessengerBusAccessor implements MessengerBusAccessor
{

	public function __construct(
		private ?CommandBus $commandBus = null,
		private ?QueryBus $queryBus = null,
		private ?EventBus $eventBus = null,
	)
	{
	}

	public function getCommandBus(): MessageBus
	{
		return $this->commandBus ?? throw new RuntimeException('Command bus not set.');
	}

	public function hasCommandBus(): bool
	{
		return $this->commandBus !== null;
	}

	public function getQueryBus(): MessageBus
	{
		return $this->queryBus ?? throw new RuntimeException('Query bus not set.');
	}

	public function hasQueryBus(): bool
	{
		return $this->queryBus !== null;
	}

	public function getEventBus(): MessageBus
	{
		return $this->eventBus ?? throw new RuntimeException('Event bus not set.');
	}

	public function hasEventBus(): bool
	{
		return $this->eventBus !== null;
	}

}
