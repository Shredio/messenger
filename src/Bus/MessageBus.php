<?php declare(strict_types = 1);

namespace Shredio\Messenger\Bus;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

abstract class MessageBus
{

	public function __construct(
		public readonly MessageBusInterface $original,
	)
	{
	}

	/**
	 * Dispatches the given message.
	 *
	 * @param object|Envelope  $message The message or the message pre-wrapped in an envelope
	 * @param StampInterface[] $stamps
	 *
	 * @throws ExceptionInterface
	 */
	public function dispatch(object $message, array $stamps = []): Envelope
	{
		return $this->original->dispatch($message, $stamps);
	}

}
