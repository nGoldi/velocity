<?php
declare(strict_types=1);

namespace App\Command;

use App\Domain\Ports\Inbound\IVelocity;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:output-velocity',
    description: 'Outputs all velocity data.',
    aliases: ['app:velocity'],
    hidden: false
)]
class VelocityOutputCommand extends Command // todo move to Application. register in services.yaml
{
    public function __construct(private readonly IVelocity $velocityService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        // the full command description shown when running the command with the "--help" option
        $this->setHelp('This command allows you display all sprint data the app provides.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // logic to be executed goes here
        $velocity = $this->velocityService->nextSprintVelocity();
        $output->writeln((string)$velocity);

        return Command::SUCCESS;
    }
}
