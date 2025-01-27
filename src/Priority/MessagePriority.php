<?php

namespace Shredio\Messenger\Priority;

enum MessagePriority
{

	case Normal;
	case Important;

	public function getSlug(): string
	{
		return match ($this) {
			self::Normal => '',
			self::Important => 'important',
		};
	}

}
