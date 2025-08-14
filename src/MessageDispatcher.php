<?php declare(strict_types = 1);

namespace Shredio\Messenger;

use Shredio\Messenger\Message\RoutableMessage;
use Symfony\Component\Messenger\Stamp\StampInterface;

interface MessageDispatcher
{

	/**
	 * @param array<int, StampInterface> $stamps
	 */
	public function dispatch(RoutableMessage $message, array $stamps = []): void;

}
