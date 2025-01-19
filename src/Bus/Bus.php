<?php declare(strict_types = 1);

namespace Shredio\Messenger\Bus;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

abstract class Bus implements MessageBusInterface
{

	public function __construct(
		private readonly MessageBusInterface $bus,
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
		return $this->bus->dispatch($message, $stamps);
	}

}
