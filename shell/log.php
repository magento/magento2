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
 * @package     Mage_Shell
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'abstract.php';

/**
 * Magento Log Shell Script
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Shell_Log extends Mage_Shell_Abstract
{
    /**
     * Log instance
     *
     * @var Mage_Log_Model_Log
     */
    protected $_log;

    /**
     * Retrieve Log instance
     *
     * @return Mage_Log_Model_Log
     */
    protected function _getLog()
    {
        if (is_null($this->_log)) {
            $this->_log = Mage::getModel('Mage_Log_Model_Log');
        }
        return $this->_log;
    }

    /**
     * Convert count to human view
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
     * Convert size to human view
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
     * Run script
     *
     */
    public function run()
    {
        if ($this->getArg('clean')) {
            $days = $this->getArg('days');
            if ($days > 0) {
                Mage::app()->getStore()->setConfig(Mage_Log_Model_Log::XML_LOG_CLEAN_DAYS, $days);
            }
            $this->_getLog()->clean();
            echo "Log cleaned\n";
        } else if ($this->getArg('status')) {
            $resource = $this->_getLog()->getResource();
            $adapter  = $resource->getReadConnection();
            // log tables
            $tables = array(
                $resource->getTable('log_customer'),
                $resource->getTable('log_visitor'),
                $resource->getTable('log_visitor_info'),
                $resource->getTable('log_url_table'),
                $resource->getTable('log_url_info_table'),
                $resource->getTable('log_quote_table'),

                $resource->getTable('reports_viewed_product_index'),
                $resource->getTable('reports_compared_product_index'),
                $resource->getTable('reports_event'),

                $resource->getTable('catalog_compare_item'),
            );

            $rows        = 0;
            $dataLengh   = 0;
            $indexLength = 0;

            $line = '-----------------------------------+------------+------------+------------+' . "\n";
            echo $line;
            echo sprintf('%-35s|', 'Table Name');
            echo sprintf(' %-11s|', 'Rows');
            echo sprintf(' %-11s|', 'Data Size');
            echo sprintf(' %-11s|', 'Index Size');
            echo "\n";
            echo $line;

            foreach ($tables as $table) {
                $query  = $adapter->quoteInto('SHOW TABLE STATUS LIKE ?', $table);
                $status = $adapter->fetchRow($query);
                if (!$status) {
                    continue;
                }

                $rows += $status['Rows'];
                $dataLengh += $status['Data_length'];
                $indexLength += $status['Index_length'];

                echo sprintf('%-35s|', $table);
                echo sprintf(' %-11s|', $this->_humanCount($status['Rows']));
                echo sprintf(' %-11s|', $this->_humanSize($status['Data_length']));
                echo sprintf(' %-11s|', $this->_humanSize($status['Index_length']));
                echo "\n";
            }

            echo $line;
            echo sprintf('%-35s|', 'Total');
            echo sprintf(' %-11s|', $this->_humanCount($rows));
            echo sprintf(' %-11s|', $this->_humanSize($dataLengh));
            echo sprintf(' %-11s|', $this->_humanSize($indexLength));
            echo "\n";
            echo $line;
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f log.php -- [options]
        php -f log.php -- clean --days 1

  clean             Clean Logs
  --days <days>     Save log, days. (Minimum 1 day, if defined - ignoring system value)
  status            Display statistics per log tables
  help              This help

USAGE;
    }
}

$shell = new Mage_Shell_Log();
$shell->run();
