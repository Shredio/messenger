## Installation

Nette

```neon
services:
	- Shredio\Messenger\Command\ConsumeCronMessagesCommand(
		@messenger.transport.container,
		@messenger.bus.routable,
		@messenger.event.dispatcher,
		@messenger.logger.logger
	)
	- Shredio\Messenger\Doctrine\RootEntityMessageDispatcher
	- Shredio\Messenger\Bus\DefaultMessengerBusAccessor
	- Shredio\Messenger\Bus\DefaultRoutableBus
	- Shredio\Messenger\DefaultMessageDispatcher('root', ['stocks'])

messenger:
	bus:
		commandBus:
			middlewares:
				- Shredio\Messenger\Middleware\DiscardableMessageMiddleware(@messenger.logger.logger)
```

## Usage

Command Message:

```php
use Shredio\Messenger\Message\CommandMessage;

class MyCommandMessage implements CommandMessage
{
}

```

Event Message:

```php
use Shredio\Messenger\Message\EventMessage;

class MyEventMessage implements EventMessage
{
}

```

Query Message:

```php
use Shredio\Messenger\Message\QueryMessage;

class MyQueryMessage implements QueryMessage
{
}

```

By default, all messages are synchronous and private. If you want to make a message asynchronous, you can do so by implementing the `AsynchronousMessage` interface.

```php
use Shredio\Messenger\Message\AsynchronousMessage;

class MyAsyncCommandMessage implements CommandMessage, AsynchronousMessage
{
}

```

If you want to make a message public, you can do so by implementing the `PublicMessage` interface.

```php
use Shredio\Messenger\Message\PublicMessage;

class MyPublicCommandMessage implements CommandMessage, PublicMessage
{
}

```

## Prioritization

You can prioritize messages by implementing the `PriorityAwareMessage` interface.

```php
use Shredio\Messenger\Message\PriorityAwareMessage;

class MyPriorityCommandMessage implements CommandMessage, PriorityAwareMessage
{

    public function getPriority(): MessagePriority
    {
        return MessagePriority::Important;
    }
}

```
