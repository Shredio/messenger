<?php declare(strict_types = 1);

namespace Shredio\Messenger;

use Shredio\Messenger\Message\ConfigureMessage;
use Shredio\Messenger\Message\RoutableMessage;

interface MessageDispatcher
{

	public function dispatch(RoutableMessage $message, ?ConfigureMessage $config = null): void;

}
