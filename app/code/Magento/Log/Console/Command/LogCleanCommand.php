<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Log\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command for displaying status of Magento logs
 */
class LogCleanCommand extends AbstractLogCommand
{
    /**
     * Names of input option
     */
    const INPUT_KEY_DAYS = 'days';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_DAYS,
                null,
                InputOption::VALUE_REQUIRED,
                'Save log for specified number of days (minimum 1 day)'
            ),
        ];
        $this->setName('log:clean')
            ->setDescription('Cleans Logs')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $days = $input->getOption(self::INPUT_KEY_DAYS);
        /** @var \Magento\Log\Model\Shell\Command\Clean $command */
        $command = $this->objectManager->create('Magento\Log\Model\Shell\Command\Clean', ['days' => $days]);
        $outputMsg = $command->execute();
        $output->writeln('<info>' . $outputMsg . '</info>');
    }
}