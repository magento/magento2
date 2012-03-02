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
 * @category    Mage
 * @package     Mage_Log
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shell model, used to work with logs via command line
 *
 * @category    Mage
 * @package     Mage_Log
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Log_Model_Shell extends Mage_Core_Model_ShellAbstract
{
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
        if ($number < 1000) {
            return sprintf('%d b', $number);
        } else if ($number >= 1000 && $number < 1000000) {
            return sprintf('%.2fKb', $number / 1000);
        } else if ($number >= 1000000 && $number < 1000000000) {
            return sprintf('%.2fMb', $number / 1000000);
        } else {
            return sprintf('%.2fGb', $number / 1000000000);
        }
    }

    /**
     * Runs script
     *
     * @return Mage_Log_Model_Shell
     */
    public function run()
    {
        if ($this->_showHelp()) {
            return $this;
        }

        if ($this->getArg('clean')) {
            $days = $this->getArg('days');
            if ($days > 0) {
                Mage::app()->getStore()->setConfig(Mage_Log_Model_Log::XML_LOG_CLEAN_DAYS, $days);
            }
            /** @var $model Mage_Log_Model_Log */
            $model = Mage::getModel('Mage_Log_Model_Log');
            $model->clean();
            echo "Log cleaned\n";
        } else if ($this->getArg('status')) {
            /** @var $resource Mage_Log_Model_Resource_Shell */
            $resource = Mage::getModel('Mage_Log_Model_Resource_Shell');
            $tables = $resource->getTablesInfo();

            $line = '-----------------------------------+------------+------------+------------+' . "\n";
            echo $line;
            echo sprintf('%-35s|', 'Table Name');
            echo sprintf(' %-11s|', 'Rows');
            echo sprintf(' %-11s|', 'Data Size');
            echo sprintf(' %-11s|', 'Index Size');
            echo "\n";
            echo $line;

            $rows = 0;
            $dataLength = 0;
            $indexLength = 0;
            foreach ($tables as $table) {
                $rows += $table['rows'];
                $dataLength += $table['data_length'];
                $indexLength += $table['index_length'];

                echo sprintf('%-35s|', $table['name']);
                echo sprintf(' %-11s|', $this->_humanCount($table['rows']));
                echo sprintf(' %-11s|', $this->_humanSize($table['data_length']));
                echo sprintf(' %-11s|', $this->_humanSize($table['index_length']));
                echo "\n";
            }

            echo $line;
            echo sprintf('%-35s|', 'Total');
            echo sprintf(' %-11s|', $this->_humanCount($rows));
            echo sprintf(' %-11s|', $this->_humanSize($dataLength));
            echo sprintf(' %-11s|', $this->_humanSize($indexLength));
            echo "\n";
            echo $line;
        } else {
            echo $this->getUsageHelp();
        }

        return $this;
    }

    /**
     * Retrieves usage help message
     *
     * @return string
     */
    public function getUsageHelp()
    {
        return <<<USAGE
Usage:  php -f {$this->_entryPoint} -- [options]
        php -f {$this->_entryPoint} -- clean --days 1

  clean             Clean Logs
  --days <days>     Save log, days. (Minimum 1 day, if defined - ignoring system value)
  status            Display statistics per log tables
  help              This help

USAGE;
    }
}
