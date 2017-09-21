<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Troubleshooting;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Perform all checks.
 */
class GlobalAnalyzer extends \Symfony\Component\Console\Command\Command
{
    /**
     * List of commands.
     *
     * @var \Magento\Mtf\Console\CommandList
     */
    private $commandList;

    /**
     * @param \Symfony\Component\Console\Command\Command[] $commandList
     */
    public function __construct(
        $commandList
    ) {
        parent::__construct();
        $this->commandList = $commandList;
    }

    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('troubleshooting:check-all')
            ->setDescription('Perform all available checks.');
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->commandList as $command) {
            $command->execute($input, $output);
            $output->writeln('');
        }
    }
}
