<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\Console\Command;

use Magento\Backend\Model\Cache\WarmupRunner;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\Console\Cli;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractCacheTypeManageCommand extends AbstractCacheManageCommand
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var WarmupRunner
     */
    private $cacheWarmupRunner;

    /**
     * @param Manager $cacheManager
     * @param EventManagerInterface $eventManager
     * @param WarmupRunner $cacheWarmupRunner
     */
    public function __construct(
        Manager $cacheManager,
        EventManagerInterface $eventManager,
        WarmupRunner $cacheWarmupRunner
    ) {
        $this->eventManager = $eventManager;
        $this->cacheWarmupRunner = $cacheWarmupRunner;
        parent::__construct($cacheManager);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        parent::configure();
        $this->addOption(
            'warmup',
            'w',
            InputOption::VALUE_NONE,
            'After this operation, send HTTP GET requests to warm full-page cache '
            . '(Stores > Configuration > Advanced > Developer > CLI Cache Warmup).'
        );
    }

    /**
     * Perform a cache management action on cache types
     *
     * @param array $cacheTypes
     * @return void
     */
    abstract protected function performAction(array $cacheTypes);

    /**
     * Get display message
     *
     * @return string
     */
    abstract protected function getDisplayMessage();

    /**
     * Perform cache management action
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $types = $this->getRequestedTypes($input);
        $this->performAction($types);
        $output->writeln($this->getDisplayMessage());
        $output->writeln(join(PHP_EOL, $types));

        if ($input->getOption('warmup')) {
            $this->cacheWarmupRunner->run($output);
        }

        return Cli::RETURN_SUCCESS;
    }
}
