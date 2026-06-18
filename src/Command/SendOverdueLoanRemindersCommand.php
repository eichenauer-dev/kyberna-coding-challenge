<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\ProcessOverdueLoanReminders;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Dispatches the overdue loan reminder process via Symfony Messenger.
 */
#[AsCommand(
    name: 'app:reminders:process',
    description: 'Dispatch overdue loan reminders to be stored in the database',
)]
final class SendOverdueLoanRemindersCommand extends Command
{
    /**
     * Initializes the SendOverdueLoanRemindersCommand instance.
     * 
     * @param MessageBusInterface $messageBus
     *
     * @return void
     */
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    /**
     * Dispatches the reminder processing message.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->messageBus->dispatch(new ProcessOverdueLoanReminders());

        $io->success('Overdue loan reminder processing has been dispatched.');

        return Command::SUCCESS;
    }
}
