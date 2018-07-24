<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Helper\Dashboard;

/**
 * Adminhtml dashboard helper for orders
 *
 * @api
 * @since 100.0.2
 */
class Order extends \Magento\Backend\Helper\Dashboard\AbstractDashboard
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Order\Collection
     */
    private $orderCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 100.0.6
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Reports\Model\ResourceModel\Order\Collection $orderCollection
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Reports\Model\ResourceModel\Order\Collection $orderCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->orderCollection = $orderCollection;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _initCollection()
    {
        $isFilter = $this->getParam('store') || $this->getParam('website') || $this->getParam('group');

        $this->_collection = $this->orderCollection->prepareSummary($this->getParam('period'), 0, 0, $isFilter);

        if ($this->getParam('store')) {
            $this->_collection->addFieldToFilter('store_id', $this->getParam('store'));
        } elseif ($this->getParam('website')) {
            $storeIds = $this->storeManager->getWebsite($this->getParam('website'))->getStoreIds();
            $this->_collection->addFieldToFilter('store_id', ['in' => implode(',', $storeIds)]);
        } elseif ($this->getParam('group')) {
            $storeIds = $this->storeManager->getGroup($this->getParam('group'))->getStoreIds();
            $this->_collection->addFieldToFilter('store_id', ['in' => implode(',', $storeIds)]);
        } elseif (!$this->_collection->isLive()) {
            $this->_collection->addFieldToFilter(
                'store_id',
                ['eq' => $this->storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId()]
            );
        }
        $this->_collection->load();
    }
}
