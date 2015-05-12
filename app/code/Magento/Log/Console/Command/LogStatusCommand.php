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
     * Output data
     *
     * @var array
     */
    private $output = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('log:status')->setDescription('Displays statistics per log tables');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Magento\Log\Model\Resource\ShellFactory $resourceFactory */
        $resourceFactory = $this->objectManager->create('Magento\Log\Model\Resource\ShellFactory');
        /** @var \Magento\Log\Model\Resource\Shell $resource */
        $resource = $resourceFactory->create();
        $tables = $resource->getTablesInfo();

        $this->addRowDelimiter();
        $line = sprintf('%-35s|', 'Table Name');
        $line .= sprintf(' %-11s|', 'Rows');
        $line .= sprintf(' %-11s|', 'Data Size');
        $line .= sprintf(' %-11s|', 'Index Size');
        $this->addOutput($line);
        $this->addRowDelimiter();

        $rows = 0;
        $dataLength = 0;
        $indexLength = 0;
        foreach ($tables as $table) {
            $rows += $table['rows'];
            $dataLength += $table['data_length'];
            $indexLength += $table['index_length'];

            $line = sprintf('%-35s|', $table['name']);
            $line .= sprintf(' %-11s|', $this->humanCount($table['rows']));
            $line .= sprintf(' %-11s|', $this->humanSize($table['data_length']));
            $line .= sprintf(' %-11s|', $this->humanSize($table['index_length']));
            $this->addOutput($line);
        }

        $this->addRowDelimiter();
        $line = sprintf('%-35s|', 'Total');
        $line .= sprintf(' %-11s|', $this->humanCount($rows));
        $line .= sprintf(' %-11s|', $this->humanSize($dataLength));
        $line .= sprintf(' %-11s|', $this->humanSize($indexLength));
        $this->addOutput($line);
        $this->addRowDelimiter();

        $output->writeln('<info>' . $this->getOutput() . '</info>');
    }

    /**
     * Add output data
     *
     * @param string $output
     * @return void
     */
    private function addOutput($output)
    {
        $this->output[] = $output;
    }

    /**
     * Get output data
     *
     * @return string
     */
    private function getOutput()
    {
        return implode("\n", $this->output);
    }

    /**
     * Converts count to human view
     *
     * @param int $number
     * @return string
     */
    private function humanCount($number)
    {
        if ($number < 1000) {
            return $number;
        } elseif ($number >= 1000 && $number < 1000000) {
            return sprintf('%.2fK', $number / 1000);
        } elseif ($number >= 1000000 && $number < 1000000000) {
            return sprintf('%.2fM', $number / 1000000);
        } else {
            return sprintf('%.2fB', $number / 1000000000);
        }
    }

    /**
     * Converts size to human view
     *
     * @param int $number
     * @return string
     */
    private function humanSize($number)
    {
        if ($number < 1024) {
            return sprintf('%d b', $number);
        } elseif ($number >= 1024 && $number < (1024 * 1024)) {
            return sprintf('%.2fKb', $number / 1024);
        } elseif ($number >= (1024 * 1024) && $number < (1024 * 1024 * 1024)) {
            return sprintf('%.2fMb', $number / (1024 * 1024));
        } else {
            return sprintf('%.2fGb', $number / (1024 * 1024 * 1024));
        }
    }

    /**
     * Add row delimiter
     *
     * @return void
     */
    private function addRowDelimiter()
    {
        $this->addOutput('-----------------------------------+------------+------------+------------+');
    }
}
