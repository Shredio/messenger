<?php declare(strict_types = 1);

namespace Shredio\Messenger\Bus;

use InvalidArgumentException;
use Shredio\Messenger\Message\CommandMessage;
use Shredio\Messenger\Message\EventMessage;
use Shredio\Messenger\Message\QueryMessage;
use Shredio\Messenger\Message\RoutableMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

final readonly class DefaultRoutableBus implements RoutableBus
{

	public function __construct(
		private MessengerBusAccessor $accessor,
	)
	{
	}

	/**
	 * Dispatches the given message.
	 *
	 * @param StampInterface[] $stamps
	 *
	 * @throws ExceptionInterface
	 */
	public function dispatch(RoutableMessage $message, array $stamps = []): Envelope
	{
		if ($message instanceof EventMessage) {
			return $this->accessor->getEventBus()->dispatch($message, $stamps);
		}

		if ($message instanceof CommandMessage) {
			return $this->accessor->getCommandBus()->dispatch($message, $stamps);
		}

		if ($message instanceof QueryMessage){
			return $this->accessor->getQueryBus()->dispatch($message, $stamps);
		}

		self::throwInvalidType($message);
	}

	private static function throwInvalidType(object $message): never
	{
		throw new InvalidArgumentException(
			sprintf(
				'Message must be routable message instance of %s, %s given.',
				RoutableMessage::class,
				get_debug_type($message),
			),
		);
	}

}
