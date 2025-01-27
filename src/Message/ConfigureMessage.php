<?php declare(strict_types = 1);

namespace Shredio\Messenger\Message;

use Shredio\Messenger\Priority\MessagePriority;

final readonly class ConfigureMessage
{

	public function __construct(
		public ?MessagePriority $priority = null,
	)
	{
	}

}
