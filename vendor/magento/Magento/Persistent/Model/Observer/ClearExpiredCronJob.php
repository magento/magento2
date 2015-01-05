<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Persistent\Model\Observer;

class ClearExpiredCronJob
{
    /**
     * Website collection factory
     *
     * @var \Magento\Store\Model\Resource\Website\CollectionFactory
     */
    protected $_websiteCollectionFactory;

    /**
     * Session factory
     *
     * @var \Magento\Persistent\Model\SessionFactory
     */
    protected $_sessionFactory;

    /**
     * @param \Magento\Store\Model\Resource\Website\CollectionFactory $websiteCollectionFactory
     * @param \Magento\Persistent\Model\SessionFactory $sessionFactory
     */
    public function __construct(
        \Magento\Store\Model\Resource\Website\CollectionFactory $websiteCollectionFactory,
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
