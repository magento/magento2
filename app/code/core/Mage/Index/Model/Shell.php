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
 * @package     Mage_Index
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shell model, used to work with indexers via command line
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Index_Model_Shell extends Mage_Core_Model_Shell_Abstract
{
    /**
     * Gets indexer instance
     *
     * @return Mage_Index_Model_Indexer
     */
    protected function _getIndexer()
    {
        return Mage::getSingleton('Mage_Index_Model_Indexer');
    }

    /**
     * Parses string with indexers and return array of indexer instances
     *
     * @param string $string
     * @return array
     */
    protected function _parseIndexerString($string)
    {
        $processes = array();
        if ($string == 'all') {
            $collection = $this->_getIndexer()->getProcessesCollection();
            foreach ($collection as $process) {
                $processes[] = $process;
            }
        } else if (!empty($string)) {
            $codes = explode(',', $string);
            foreach ($codes as $code) {
                $process = $this->_getIndexer()->getProcessByCode(trim($code));
                if (!$process) {
                    echo 'Warning: Unknown indexer with code ' . trim($code) . "\n";
                } else {
                    $processes[] = $process;
                }
            }
        }
        return $processes;
    }

    /**
     * Runs this model, assumed to be run by command-line
     *
     * @return Mage_Index_Model_Shell
     */
    public function run()
    {
        if ($this->_showHelp()) {
            return $this;
        }

        if ($this->getArg('info')) {
            $this->_runShowInfo();
        } else if ($this->getArg('status') || $this->getArg('mode')) {
            $this->_runShowStatusOrMode();
        } else if ($this->getArg('mode-realtime') || $this->getArg('mode-manual')) {
            $this->_runSetMode();
        } else if ($this->getArg('reindex') || $this->getArg('reindexall')) {
            $this->_runReindex();
        } else {
            echo $this->getUsageHelp();
        }
        return $this;
    }

    /**
     * Shows information about indexes
     *
     * @return Mage_Index_Model_Shell
     */
    protected function _runShowInfo()
    {
        $processes = $this->_parseIndexerString('all');
        foreach ($processes as $process) {
            /* @var $process Mage_Index_Model_Process */
            echo sprintf('%-30s', $process->getIndexerCode());
            echo $process->getIndexer()->getName() . "\n";
        }
        return $this;
    }

    /**
     * Shows information about statuses or modes
     *
     * @return Mage_Index_Model_Shell
     */
    protected function _runShowStatusOrMode()
    {
        if ($this->getArg('status')) {
            $processes  = $this->_parseIndexerString($this->getArg('status'));
        } else {
            $processes  = $this->_parseIndexerString($this->getArg('mode'));
        }
        foreach ($processes as $process) {
            /* @var $process Mage_Index_Model_Process */
            $status = 'unknown';
            if ($this->getArg('status')) {
                switch ($process->getStatus()) {
                    case Mage_Index_Model_Process::STATUS_PENDING:
                        $status = 'Pending';
                        break;
                    case Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX:
                        $status = 'Require Reindex';
                        break;

                    case Mage_Index_Model_Process::STATUS_RUNNING:
                        $status = 'Running';
                        break;

                    default:
                        $status = 'Ready';
                        break;
                }
            } else {
                switch ($process->getMode()) {
                    case Mage_Index_Model_Process::MODE_REAL_TIME:
                        $status = 'Update on Save';
                        break;
                    case Mage_Index_Model_Process::MODE_MANUAL:
                        $status = 'Manual Update';
                        break;
                }
            }
            echo sprintf('%-30s ', $process->getIndexer()->getName() . ':') . $status ."\n";
        }
        return $this;
    }

    /**
     * Sets new mode for indexes
     *
     * @return Mage_Index_Model_Shell
     */
    protected function _runSetMode()
    {
        if ($this->getArg('mode-realtime')) {
            $mode       = Mage_Index_Model_Process::MODE_REAL_TIME;
            $processes  = $this->_parseIndexerString($this->getArg('mode-realtime'));
        } else {
            $mode       = Mage_Index_Model_Process::MODE_MANUAL;
            $processes  = $this->_parseIndexerString($this->getArg('mode-manual'));
        }
        foreach ($processes as $process) {
            /* @var $process Mage_Index_Model_Process */
            try {
                $process->setMode($mode)->save();
                echo $process->getIndexer()->getName() . " index was successfully changed index mode\n";
            } catch (Mage_Core_Exception $e) {
                echo $e->getMessage() . "\n";
            } catch (Exception $e) {
                echo $process->getIndexer()->getName() . " index process unknown error:\n";
                echo $e . "\n";
            }
        }
        return $this;
    }

    /**
     * Reindexes indexer(s)
     *
     * @return Mage_Index_Model_Shell
     */
    protected function _runReindex()
    {
        if ($this->getArg('reindex')) {
            $processes = $this->_parseIndexerString($this->getArg('reindex'));
        } else {
            $processes = $this->_parseIndexerString('all');
        }

        foreach ($processes as $process) {
            /* @var $process Mage_Index_Model_Process */
            try {
                $process->reindexEverything();
                echo $process->getIndexer()->getName() . " index was rebuilt successfully\n";
            } catch (Mage_Core_Exception $e) {
                echo $e->getMessage() . "\n";
            } catch (Exception $e) {
                echo $process->getIndexer()->getName() . " index process unknown error:\n";
                echo $e . "\n";
            }
        }
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

  --status <indexer>            Show Indexer(s) Status
  --mode <indexer>              Show Indexer(s) Index Mode
  --mode-realtime <indexer>     Set index mode type "Update on Save"
  --mode-manual <indexer>       Set index mode type "Manual Update"
  --reindex <indexer>           Reindex Data
  info                          Show allowed indexers
  reindexall                    Reindex Data by all indexers
  help                          This help

  <indexer>     Comma separated indexer codes or value "all" for all indexers
USAGE;
    }
}
