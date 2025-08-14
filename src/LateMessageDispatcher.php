<?php declare(strict_types = 1);

namespace Shredio\Messenger;

use Shredio\Messenger\Message\RoutableMessage;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class LateMessageDispatcher implements MessageDispatcher
{

	/** @var array{RoutableMessage, array<int, StampInterface>}[] */
	private array $stack = [];

	public function __construct(
		private readonly MessageDispatcher $parent,
	)
	{
	}

	public function dispatch(RoutableMessage $message, array $stamps = []): void
	{
		$this->stack[] = [$message, $stamps];
	}

	public function execute(): void
	{
		foreach ($this->stack as [$message, $stamps]) {
			$this->parent->dispatch($message, $stamps);
		}
	}

}
