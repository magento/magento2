<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model;

/**
 * Shell model, used to work with logs via command line
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Shell extends \Magento\Framework\App\AbstractShell
{
    /**
     * @var \Magento\Log\Model\Shell\Command\Factory
     */
    protected $_commandFactory;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $entryPoint
     * @param \Magento\Log\Model\Shell\Command\Factory $commandFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        $entryPoint,
        \Magento\Log\Model\Shell\Command\Factory $commandFactory
    ) {
        parent::__construct($filesystem, $entryPoint);
        $this->_commandFactory = $commandFactory;
    }

    /**
     * Runs script
     *
     * @return $this
     */
    public function run()
    {
        if ($this->_showHelp()) {
            return $this;
        }

        if ($this->getArg('clean')) {
            $output = $this->_commandFactory->createCleanCommand($this->getArg('days'))->execute();
        } elseif ($this->getArg('status')) {
            $output = $this->_commandFactory->createStatusCommand()->execute();
        } else {
            $output = $this->getUsageHelp();
        }

        echo $output;

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
