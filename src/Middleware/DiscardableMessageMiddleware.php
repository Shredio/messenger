<?php declare(strict_types = 1);

namespace Shredio\Messenger\Middleware;

use Psr\Log\LoggerInterface;
use Shredio\Messenger\Message\DiscardableMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Throwable;

final readonly class DiscardableMessageMiddleware implements MiddlewareInterface
{

	public function __construct(
		private ?LoggerInterface $logger = null,
	)
	{
	}

	public function handle(Envelope $envelope, StackInterface $stack): Envelope
	{
		try {
			return $stack->next()->handle($envelope, $stack);
		} catch (Throwable $throwable) {
			$message = $envelope->getMessage();

			if ($message instanceof DiscardableMessage) {
				if ($throwable instanceof NoHandlerForMessageException) {
					$this->logger?->info(
						'No handler found for discardable message {class}. Removing from transport.',
						['class' => $message::class]
					);
				} else {
					$this->logger?->critical(
						'Error thrown while handling message {class} (Discardable message). Removing from transport. Error: "{error}"',
						['class' => $message::class, 'error' => $throwable->getMessage(), 'exception' => $throwable]
					);
				}

				return $envelope->with(new HandledStamp(null, 'discarded'));
			}

			throw $throwable;
		}
	}

}
