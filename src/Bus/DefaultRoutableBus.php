<?php declare(strict_types = 1);

namespace Shredio\Messenger\Bus;

use InvalidArgumentException;
use Shredio\Messenger\Message\CommandMessage;
use Shredio\Messenger\Message\EventMessage;
use Shredio\Messenger\Message\QueryMessage;
use Shredio\Messenger\Message\RoutableMessage;
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
	public function dispatch(RoutableMessage $message, array $stamps = []): void
	{
		if ($message instanceof EventMessage) {
			$this->accessor->getEventBus()->dispatch($message, $stamps);

			return;
		}

		if ($message instanceof CommandMessage) {
			$this->accessor->getCommandBus()->dispatch($message, $stamps);

			return;
		}

		if ($message instanceof QueryMessage){
			$this->accessor->getQueryBus()->dispatch($message, $stamps);

			return;
		}

		self::throwInvalidType($message);
	}

	public static function getBusName(RoutableMessage $message): string
	{
		if ($message instanceof EventMessage) {
			return 'event';
		}

		if ($message instanceof CommandMessage) {
			return 'command';
		}

		if ($message instanceof QueryMessage) {
			return 'query';
		}

		return 'unknown';
	}

	public function hasBusFor(RoutableMessage $message): bool
	{
		if ($message instanceof EventMessage) {
			return $this->accessor->hasEventBus();
		}

		if ($message instanceof CommandMessage) {
			return $this->accessor->hasCommandBus();
		}

		if ($message instanceof QueryMessage) {
			return $this->accessor->hasQueryBus();
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
