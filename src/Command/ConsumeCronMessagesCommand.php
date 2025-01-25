<?php declare(strict_types = 1);

namespace Shredio\Messenger\Command;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Messenger\Worker;

#[AsCommand('messenger:consume-cron', description: 'Consume messages once in cron job')]
final class ConsumeCronMessagesCommand extends Command
{

	/**
	 * @param array<string> $receiverNames
	 */
	public function __construct(
		private readonly ContainerInterface $receiverLocator,
		private readonly RoutableMessageBus $routableBus,
		private readonly EventDispatcherInterface $eventDispatcher,
		private readonly ?LoggerInterface $logger = null,
		private readonly ?ContainerInterface $rateLimiterLocator = null,
		private readonly array $receiverNames = [],
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$defaultReceiverName = count($this->receiverNames) === 1 ? current($this->receiverNames) : null;

		$this->addArgument('receivers', InputArgument::IS_ARRAY, 'Names of the receivers/transports to consume in order of priority', $defaultReceiverName ? [$defaultReceiverName] : []);
	}

	protected function interact(InputInterface $input, OutputInterface $output): void
	{
		$io = new SymfonyStyle($input, $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output);

		if ($this->receiverNames && !$input->getArgument('receivers')) {
			$io->block('Which transports/receivers do you want to consume?', null, 'fg=white;bg=blue', ' ', true);

			$io->writeln('Choose which receivers you want to consume messages from in order of priority.');
			if (\count($this->receiverNames) > 1) {
				$io->writeln(sprintf('Hint: to consume from multiple, use a list of their names, e.g. <comment>%s</comment>', implode(', ', $this->receiverNames)));
			}

			$question = new ChoiceQuestion('Select receivers to consume:', $this->receiverNames, 0);
			$question->setMultiselect(true);

			$input->setArgument('receivers', $io->askQuestion($question));
		}

		if (!$input->getArgument('receivers')) {
			throw new RuntimeException('Please pass at least one receiver.');
		}
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$receivers = [];
		$rateLimiters = [];
		foreach ($input->getArgument('receivers') as $receiverName) {
			if (!$this->receiverLocator->has($receiverName)) {
				$message = sprintf('The receiver "%s" does not exist.', $receiverName);
				if ($this->receiverNames) {
					$message .= sprintf(' Valid receivers are: %s.', implode(', ', $this->receiverNames));
				}

				throw new RuntimeException($message);
			}

			$receivers[$receiverName] = $this->receiverLocator->get($receiverName);
			if ($this->rateLimiterLocator?->has($receiverName)) {
				$rateLimiters[$receiverName] = $this->rateLimiterLocator->get($receiverName);
			}
		}

		$this->eventDispatcher->addListener(WorkerRunningEvent::class, [$this, 'workerRunning']);

		$worker = new Worker($receivers, $this->routableBus, $this->eventDispatcher, $this->logger, $rateLimiters);
		$options = ['sleep' => 0];

		$worker->run($options);

		return self::SUCCESS;
	}

	public function workerRunning(WorkerRunningEvent $event): void
	{
		if ($event->isWorkerIdle()) {
			$event->getWorker()->stop();
		}
	}

}
