<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model\Shell\Command;

class Status implements \Magento\Log\Model\Shell\CommandInterface
{
    /**
     * @var \Magento\Log\Model\Resource\ShellFactory
     */
    protected $_resourceFactory;

    /**
     * Output data
     *
     * @var array
     */
    protected $_output = [];

    /**
     * @param \Magento\Log\Model\Resource\ShellFactory $resourceFactory
     */
    public function __construct(\Magento\Log\Model\Resource\ShellFactory $resourceFactory)
    {
        $this->_resourceFactory = $resourceFactory;
    }

    /**
     * Add output data
     *
     * @param string $output
     * @return void
     */
    protected function _addOutput($output)
    {
        $this->_output[] = $output;
    }

    /**
     * Get output data
     *
     * @return string
     */
    protected function _getOutput()
    {
        return implode("\n", $this->_output);
    }

    /**
     * Converts count to human view
     *
     * @param int $number
     * @return string
     */
    protected function _humanCount($number)
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
    protected function _humanSize($number)
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
    protected function _addRowDelimiter()
    {
        $this->_addOutput('-----------------------------------+------------+------------+------------+');
    }

    /**
     * Execute command
     *
     * @return string
     */
    public function execute()
    {
        /** @var $resource \Magento\Log\Model\Resource\Shell */
        $resource = $this->_resourceFactory->create();
        $tables = $resource->getTablesInfo();

        $this->_addRowDelimiter();
        $line = sprintf('%-35s|', 'Table Name');
        $line .= sprintf(' %-11s|', 'Rows');
        $line .= sprintf(' %-11s|', 'Data Size');
        $line .= sprintf(' %-11s|', 'Index Size');
        $this->_addOutput($line);
        $this->_addRowDelimiter();

        $rows = 0;
        $dataLength = 0;
        $indexLength = 0;
        foreach ($tables as $table) {
            $rows += $table['rows'];
            $dataLength += $table['data_length'];
            $indexLength += $table['index_length'];

            $line = sprintf('%-35s|', $table['name']);
            $line .= sprintf(' %-11s|', $this->_humanCount($table['rows']));
            $line .= sprintf(' %-11s|', $this->_humanSize($table['data_length']));
            $line .= sprintf(' %-11s|', $this->_humanSize($table['index_length']));
            $this->_addOutput($line);
        }

        $this->_addRowDelimiter();
        $line = sprintf('%-35s|', 'Total');
        $line .= sprintf(' %-11s|', $this->_humanCount($rows));
        $line .= sprintf(' %-11s|', $this->_humanSize($dataLength));
        $line .= sprintf(' %-11s|', $this->_humanSize($indexLength));
        $this->_addOutput($line);
        $this->_addRowDelimiter();

        return $this->_getOutput();
    }
}
