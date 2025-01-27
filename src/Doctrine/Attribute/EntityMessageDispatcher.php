<?php declare(strict_types = 1);

namespace Shredio\Messenger\Doctrine\Attribute;

use Attribute;
use Shredio\Messenger\Doctrine\EntityMessageDispatcher as EntityMessageDispatcherInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class EntityMessageDispatcher
{

	/**
	 * @param class-string<EntityMessageDispatcherInterface> $dispatcher
	 */
	public function __construct(
		public string $dispatcher,
	)
	{
	}

}
