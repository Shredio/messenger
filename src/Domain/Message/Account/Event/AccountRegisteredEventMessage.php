<?php declare(strict_types = 1);

namespace Shredio\Messenger\Domain\Message\Account\Event;

use Shredio\Messenger\Message\AsynchronousMessage;
use Shredio\Messenger\Message\EventMessage;
use Shredio\Messenger\Message\PublicMessage;

final readonly class AccountRegisteredEventMessage implements PublicMessage, AsynchronousMessage, EventMessage
{

	public function __construct(
		public int $id,
	)
	{
	}

}
