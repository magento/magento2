<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Observer;

use Magento\Cron\Model\Schedule;
use Magento\Persistent\Model\CleanExpiredPersistentQuotes;
use Magento\Persistent\Model\SessionFactory;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

class ClearExpiredCronJobObserver
{
    /**
     * A property for website collection factory
     *
     * @var CollectionFactory
     */
    protected CollectionFactory $_websiteCollectionFactory;

    /**
     * A property for session factory
     *
     * @var SessionFactory
     */
    protected SessionFactory $_sessionFactory;

    /**
     * A property for clean expired persistent quotes
     *
     * @var CleanExpiredPersistentQuotes
     */
    private CleanExpiredPersistentQuotes $cleanExpiredPersistentQuotes;

    /**
     * @param CollectionFactory $websiteCollectionFactory
     * @param SessionFactory $sessionFactory
     * @param CleanExpiredPersistentQuotes $cleanExpiredPersistentQuotes
     */
    public function __construct(
        CollectionFactory $websiteCollectionFactory,
        SessionFactory $sessionFactory,
        CleanExpiredPersistentQuotes $cleanExpiredPersistentQuotes
    ) {
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_sessionFactory = $sessionFactory;
        $this->cleanExpiredPersistentQuotes = $cleanExpiredPersistentQuotes;
    }

    /**
     * Clear expired persistent sessions
     *
     * @param Schedule $schedule
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Schedule $schedule)
    {
        $websiteIds = $this->_websiteCollectionFactory->create()->getAllIds();
        if (!is_array($websiteIds)) {
            return $this;
        }

        foreach ($websiteIds as $websiteId) {
            $this->_sessionFactory->create()->deleteExpired($websiteId);
            $this->cleanExpiredPersistentQuotes->execute((int) $websiteId);
        }

        return $this;
    }
}
