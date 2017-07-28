<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Persistent\Observer\ClearExpiredCronJobObserver
 *
 * @since 2.0.0
 */
class ClearExpiredCronJobObserver
{
    /**
     * Website collection factory
     *
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     * @since 2.0.0
     */
    protected $_websiteCollectionFactory;

    /**
     * Session factory
     *
     * @var \Magento\Persistent\Model\SessionFactory
     * @since 2.0.0
     */
    protected $_sessionFactory;

    /**
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory
     * @param \Magento\Persistent\Model\SessionFactory $sessionFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Persistent\Model\SessionFactory $sessionFactory
    ) {
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_sessionFactory = $sessionFactory;
    }

    /**
     * Clear expired persistent sessions
     *
     * @param \Magento\Cron\Model\Schedule $schedule
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(\Magento\Cron\Model\Schedule $schedule)
    {
        $websiteIds = $this->_websiteCollectionFactory->create()->getAllIds();
        if (!is_array($websiteIds)) {
            return $this;
        }

        foreach ($websiteIds as $websiteId) {
            $this->_sessionFactory->create()->deleteExpired($websiteId);
        }

        return $this;
    }
}
