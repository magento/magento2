<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\Console\Command;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Cache\Frontend\Pool as FrontendPool;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for cleaning cache
 *
 * @api
 * @since 100.0.2
 */
class CacheCleanCommand extends AbstractCacheTypeManageCommand
{
    /**
     * @var FrontendPool
     */
    private $frontendPool;

    /**
     * @param Manager $cacheManager
     * @param EventManagerInterface $eventManager
     * @param FrontendPool $frontendPool
     */
    public function __construct(
        Manager $cacheManager,
        EventManagerInterface $eventManager,
        FrontendPool $frontendPool
    ) {
        $this->frontendPool = $frontendPool;
        parent::__construct($cacheManager, $eventManager);
    }

    /**
     * Clean Magento-owned entries from every frontend when no cache types are specified.
     * A type-specific clean must not invalidate unrelated cache types.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cleanAllTypes = !$input->getArgument(self::INPUT_KEY_TYPES);
        $result = parent::execute($input, $output);

        if ($cleanAllTypes) {
            // Frontends are MAGE-tag-scoped; clean() avoids flushing shared storage.
            foreach ($this->frontendPool as $cacheFrontend) {
                $cacheFrontend->clean();
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('cache:clean');
        $this->setDescription('Cleans cache type(s)');
        parent::configure();
    }

    /**
     * Cleans cache types
     *
     * @param array $cacheTypes
     * @return void
     */
    protected function performAction(array $cacheTypes)
    {
        if ($cacheTypes === [] || in_array('full_page', $cacheTypes)) {
            $this->eventManager->dispatch('adminhtml_cache_flush_system');
        }

        $this->cacheManager->clean($cacheTypes);
    }

    /**
     * @inheritdoc
     */
    protected function getDisplayMessage()
    {
        return 'Cleaned cache types:';
    }
}
