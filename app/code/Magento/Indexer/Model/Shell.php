<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Indexer\Model;

class Shell extends \Magento\Framework\App\AbstractShell
{
    /**
     * Error status - whether errors have happened
     *
     * @var bool
     */
    protected $hasErrors = false;

    /**
     * @var Indexer\CollectionFactory
     */
    protected $indexersFactory;

    /**
     * @var IndexerFactory
     */
    protected $indexerFactory;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $entryPoint
     * @param Indexer\CollectionFactory $indexersFactory
     * @param IndexerFactory $indexerFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        $entryPoint,
        Indexer\CollectionFactory $indexersFactory,
        IndexerFactory $indexerFactory
    ) {
        $this->indexersFactory = $indexersFactory;
        $this->indexerFactory = $indexerFactory;
        parent::__construct($filesystem, $entryPoint);
    }

    /**
     * Run this model, assumed to be run by command-line
     *
     * @return \Magento\Indexer\Model\Shell
     */
    public function run()
    {
        if ($this->_showHelp()) {
            return $this;
        }

        if ($this->getArg('info')) {
            $this->runShowInfo();
        } elseif ($this->getArg('status') || $this->getArg('mode')) {
            $this->runShowStatusOrMode();
        } elseif ($this->getArg('mode-realtime') || $this->getArg('mode-schedule')) {
            $this->runSetMode();
        } elseif ($this->getArg('reindex') || $this->getArg('reindexall')) {
            $this->runReindex();
        } else {
            echo $this->getUsageHelp();
        }

        return $this;
    }

    /**
     * Show information about indexes
     *
     * @return \Magento\Indexer\Model\Shell
     */
    protected function runShowInfo()
    {
        $indexers = $this->parseIndexerString('all');
        foreach ($indexers as $indexer) {
            echo sprintf('%-40s', $indexer->getId());
            echo $indexer->getTitle() . PHP_EOL;
        }

        return $this;
    }

    /**
     * Show information about statuses or modes
     *
     * @return \Magento\Indexer\Model\Shell
     */
    protected function runShowStatusOrMode()
    {
        if ($this->getArg('status')) {
            $indexers = $this->parseIndexerString($this->getArg('status'));
        } else {
            $indexers = $this->parseIndexerString($this->getArg('mode'));
        }

        foreach ($indexers as $indexer) {
            $status = 'unknown';
            if ($this->getArg('status')) {
                switch ($indexer->getStatus()) {
                    case \Magento\Indexer\Model\Indexer\State::STATUS_VALID:
                        $status = 'Ready';
                        break;
                    case \Magento\Indexer\Model\Indexer\State::STATUS_INVALID:
                        $status = 'Reindex required';
                        break;
                    case \Magento\Indexer\Model\Indexer\State::STATUS_WORKING:
                        $status = 'Processing';
                        break;
                }
            } else {
                $status = $indexer->isScheduled() ? 'Update by Schedule' : 'Update on Save';
            }
            echo sprintf('%-50s ', $indexer->getTitle() . ':') . $status . PHP_EOL;
        }

        return $this;
    }

    /**
     * Set new mode for indexers
     *
     * @return \Magento\Indexer\Model\Shell
     */
    protected function runSetMode()
    {
        if ($this->getArg('mode-realtime')) {
            $method = 'turnViewOff';
            $indexers = $this->parseIndexerString($this->getArg('mode-realtime'));
        } else {
            $method = 'turnViewOn';
            $indexers = $this->parseIndexerString($this->getArg('mode-schedule'));
        }

        foreach ($indexers as $indexer) {
            try {
                $indexer->{$method}();
                echo $indexer->getTitle() . " indexer was successfully changed index mode" . PHP_EOL;
            } catch (\Magento\Framework\Model\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
                $this->hasErrors = true;
            } catch (\Exception $e) {
                echo $indexer->getTitle() . " indexer process unknown error:" . PHP_EOL;
                echo $e . PHP_EOL;
                $this->hasErrors = true;
            }
        }

        return $this;
    }

    /**
     * Reindex indexer(s)
     *
     * @return \Magento\Indexer\Model\Shell
     */
    protected function runReindex()
    {
        if ($this->getArg('reindex')) {
            $indexers = $this->parseIndexerString($this->getArg('reindex'));
        } else {
            $indexers = $this->parseIndexerString('all');
        }

        foreach ($indexers as $indexer) {
            try {
                $startTime = microtime(true);
                $indexer->reindexAll();
                $resultTime = microtime(true) - $startTime;
                echo $indexer->getTitle() . ' index has been rebuilt successfully in '
                    . gmdate('H:i:s', $resultTime) . PHP_EOL;
            } catch (\Magento\Framework\Model\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
                $this->hasErrors = true;
            } catch (\Exception $e) {
                echo $indexer->getTitle() . ' indexer process unknown error:' . PHP_EOL;
                echo $e . PHP_EOL;
                $this->hasErrors = true;
            }
        }

        return $this;
    }

    /**
     * Parses string with indexers and return array of indexer instances
     *
     * @param string $string
     * @return IndexerInterface[]
     */
    protected function parseIndexerString($string)
    {
        $indexers = [];
        if ($string == 'all') {
            /** @var Indexer[] $indexers */
            $indexers = $this->indexersFactory->create()->getItems();
        } elseif (!empty($string)) {
            $codes = explode(',', $string);
            foreach ($codes as $code) {
                $indexer = $this->indexerFactory->create();
                try {
                    $indexer->load($code);
                    $indexers[] = $indexer;
                } catch (\Exception $e) {
                    echo 'Warning: Unknown indexer with code ' . trim($code) . PHP_EOL;
                    $this->hasErrors = true;
                }
            }
        }
        return $indexers;
    }

    /**
     * Return whether there errors have happened
     *
     * @return bool
     */
    public function hasErrors()
    {
        return $this->hasErrors;
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
  --mode-schedule <indexer>     Set index mode type "Update by Schedule"
  --reindex <indexer>           Reindex Data
  info                          Show allowed indexers
  reindexall                    Reindex Data by all indexers
  help                          This help

  <indexer>     Comma separated indexer codes or value "all" for all indexers

USAGE;
    }
}
