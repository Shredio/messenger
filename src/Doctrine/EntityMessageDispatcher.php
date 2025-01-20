<?php declare(strict_types = 1);

namespace Shredio\Messenger\Doctrine;

interface EntityMessageDispatcher
{

	public function supports(object $object): bool;

	public function dispatchMessages(EntityMessageContext $context): void;

}
