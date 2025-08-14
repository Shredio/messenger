<?php declare(strict_types = 1);

namespace Shredio\Messenger;

use Shredio\Messenger\Bus\RoutableBus;
use Shredio\Messenger\Message\RoutableMessage;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

final readonly class DefaultMessageDispatcher implements MessageDispatcher
{

	public function __construct(
		private RoutableBus $bus,
	)
	{
	}

	/**
	 * @param array<int, StampInterface> $stamps
	 *
	 * @throws ExceptionInterface
	 */
	public function dispatch(RoutableMessage $message, array $stamps = []): void
	{
		$this->bus->dispatch($message, $stamps);
	}

}
