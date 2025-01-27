<?php declare(strict_types = 1);

namespace Shredio\Messenger;

use Shredio\Messenger\Message\ConfigureMessage;
use Shredio\Messenger\Message\RoutableMessage;

final class LateMessageDispatcher implements MessageDispatcher
{

	/** @var array{RoutableMessage, ConfigureMessage|null}[] */
	private array $stack = [];

	public function __construct(
		private readonly MessageDispatcher $parent,
	)
	{
	}

	public function dispatch(RoutableMessage $message, ?ConfigureMessage $config = null): void
	{
		$this->stack[] = [$message, $config];
	}

	public function execute(): void
	{
		foreach ($this->stack as [$message, $config]) {
			$this->parent->dispatch($message, $config);
		}
	}

}
