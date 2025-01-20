<?php declare(strict_types = 1);

namespace Shredio\Messenger\Doctrine;

enum DoctrineEvent
{

	case Persist;
	case Update;
	case Remove;

}
