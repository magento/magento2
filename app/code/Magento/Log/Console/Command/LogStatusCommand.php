<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Log\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for displaying status of Magento logs
 */
class LogStatusCommand extends AbstractLogCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('log:status')->setDescription('Displays statistics per log in a table');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputMsg = $this->commandFactory->createStatusCommand()->execute();
        $output->writeln(
            '<info>' . $outputMsg . '</info>'
        );
    }
}