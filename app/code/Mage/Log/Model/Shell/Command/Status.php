<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Log_Model_Shell_Command_Status implements Mage_Log_Model_Shell_CommandInterface
{
    /**
     * @var Mage_Log_Model_Resource_ShellFactory
     */
    protected $_resourceFactory;

    /**
     * Output data
     *
     * @var array
     */
    protected $_output = array();

    /**
     * @param Mage_Log_Model_Resource_ShellFactory $resourceFactory
     */
    public function __construct(Mage_Log_Model_Resource_ShellFactory $resourceFactory)
    {
        $this->_resourceFactory = $resourceFactory;
    }

    /**
     * Add output data
     *
     * @param string $output
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
        } else if ($number >= 1000 && $number < 1000000) {
            return sprintf('%.2fK', $number / 1000);
        } else if ($number >= 1000000 && $number < 1000000000) {
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
        } else if ($number >= 1024 && $number < (1024 * 1024)) {
            return sprintf('%.2fKb', $number / 1024);
        } else if ($number >= (1024 * 1024) && $number < (1024 * 1024 * 1024)) {
            return sprintf('%.2fMb', $number / (1024 * 1024));
        } else {
            return sprintf('%.2fGb', $number / (1024 * 1024 * 1024));
        }
    }

    /**
     * Add row delimiter
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
        /** @var $resource Mage_Log_Model_Resource_Shell */
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