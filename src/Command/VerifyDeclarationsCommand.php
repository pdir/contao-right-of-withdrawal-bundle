<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Command;

use Pdir\ContaoRightOfWithdrawalBundle\Declaration\ChainVerifier;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('pdir:withdrawal:verify', 'Checks whether stored declarations were changed or removed.')]
class VerifyDeclarationsCommand extends Command
{
    public function __construct(private readonly ChainVerifier $verifier)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $problems = $this->verifier->verify();

        if ($problems) {
            $io->error($problems);

            return Command::FAILURE;
        }

        $io->success('All declarations are intact.');

        return Command::SUCCESS;
    }
}
