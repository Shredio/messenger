<?php declare(strict_types = 1);

namespace Shredio\Messenger\Bus;

interface MessengerBusAccessor
{

	public function getCommandBus(): MessageBus;

	public function hasCommandBus(): bool;

	public function getQueryBus(): MessageBus;

	public function hasQueryBus(): bool;

	public function getEventBus(): MessageBus;
	
	public function hasEventBus(): bool;

}
