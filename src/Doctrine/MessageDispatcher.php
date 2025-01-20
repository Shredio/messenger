<?php declare(strict_types = 1);

namespace Shredio\Messenger\Doctrine;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class MessageDispatcher
{

	/**
	 * @param class-string<EntityMessageDispatcher> $dispatcher
	 */
	public function __construct(
		public string $dispatcher,
	)
	{
	}

}
