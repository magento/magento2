<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Cache\Manager;

abstract class AbstractCacheTypeManageCommand extends AbstractCacheManageCommand
{
    /** @var EventManagerInterface */
    protected $eventManager;

    /**
     * @param Manager $cacheManager
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        Manager $cacheManager,
        EventManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
        parent::__construct($cacheManager);
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
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $types = $this->getRequestedTypes($input);
        $this->performAction($types);
        $output->writeln($this->getDisplayMessage());
        $output->writeln(join(PHP_EOL, $types));
    }
}
