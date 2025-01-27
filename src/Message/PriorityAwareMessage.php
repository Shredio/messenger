<?php declare(strict_types = 1);

namespace Shredio\Messenger\Message;

use Shredio\Messenger\Priority\MessagePriority;

interface PriorityAwareMessage
{

	public function getPriority(): MessagePriority;

}
