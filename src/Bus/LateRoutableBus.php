<?php declare(strict_types = 1);

namespace Shredio\Messenger\Bus;

use InvalidArgumentException;
use Shredio\Messenger\Message\RoutableMessage;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class LateRoutableBus implements RoutableBus
{

	/** @var array{RoutableMessage, StampInterface[]}[] */
	private array $stack = [];

	public function __construct(
		private readonly RoutableBus $parent,
	)
	{
	}

	public function dispatch(RoutableMessage $message, array $stamps = []): void
	{
		if (!$this->parent->hasBusFor($message)) {
			throw new InvalidArgumentException(sprintf('No %s bus found.', DefaultRoutableBus::getBusName($message)));
		}

		$this->stack[] = [$message, $stamps];
	}

	public function hasBusFor(RoutableMessage $message): bool
	{
		return $this->parent->hasBusFor($message);
	}

	public function execute(): void
	{
		foreach ($this->stack as [$message, $stamps]) {
			$this->parent->dispatch($message, $stamps);
		}
	}

}
