<?php declare(strict_types = 1);

namespace Shredio\Messenger\Bus;

use Shredio\Messenger\Message\RoutableMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

interface RoutableBus
{

	/**
	 * Dispatches the given message.
	 *
	 * @param StampInterface[] $stamps
	 *
	 * @throws ExceptionInterface
	 */
	public function dispatch(RoutableMessage $message, array $stamps = []): Envelope;

}
