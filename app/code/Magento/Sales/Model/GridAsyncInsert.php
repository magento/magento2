<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model;

use Magento\Framework\Lock\LockManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Sales entity grids indexing observer.
 *
 * Performs handling of events and cron jobs related to indexing
 * of Order, Invoice, Shipment and Creditmemo grids.
 */
class GridAsyncInsert
{
    /**
     * Entity grid model.
     *
     * @var \Magento\Sales\Model\ResourceModel\GridInterface
     */
    protected $entityGrid;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @var LockManagerInterface|null
     */
    private $lockManager;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var string
     */
    private $lockName;

    /**
     * @param \Magento\Sales\Model\ResourceModel\GridInterface $entityGrid
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param LockManagerInterface|null $lockManager
     * @param LoggerInterface|null $logger
     * @param string $lockName
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\GridInterface $entityGrid,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ?LockManagerInterface $lockManager = null,
        ?LoggerInterface $logger = null,
        string $lockName = ''
    ) {
        $this->entityGrid = $entityGrid;
        $this->globalConfig = $globalConfig;
        $this->lockManager = $lockManager;
        $this->logger = $logger;
        $this->lockName = $lockName;
    }

    /**
     * Handles asynchronous insertion of the new entity into corresponding grid during cron job.
     *
     * Also, method is used in the next events:
     *
     * - config_data_dev_grid_async_indexing_disabled
     *
     * Works only if asynchronous grid indexing is enabled
     * in global settings.
     *
     * @return void
     */
    public function asyncInsert()
    {
        if ($this->globalConfig->getValue('dev/grid/async_indexing')) {
            if ($this->lockManager && $this->lockName !== '') {
                if (!$this->lockManager->lock($this->lockName, 0)) {
                    if ($this->logger) {
                        $this->logger->warning(
                            sprintf('Grid async insert is locked: %s, skipping run', $this->lockName)
                        );
                    }
                    return;
                }
                try {
                    $this->entityGrid->refreshBySchedule();
                } finally {
                    $this->lockManager->unlock($this->lockName);
                }
            } else {
                $this->entityGrid->refreshBySchedule();
            }
        }
    }
}
