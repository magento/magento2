<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateLinkPurchasedObserver implements ObserverInterface
{
    /**
     * Core store config
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory
     */
    protected $_purchasedFactory;

    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $_objectCopyService;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory $purchasedFactory
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory $purchasedFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_purchasedFactory = $purchasedFactory;
        $this->_objectCopyService = $objectCopyService;
    }

    /**
     * re-save order data after order update
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order->getId()) {
            //order not saved in the database
            return $this;
        }

        $purchasedLinks = $this->_createPurchasedCollection()->addFieldToFilter(
            'order_id',
            ['eq' => $order->getId()]
        );

        foreach ($purchasedLinks as $linkPurchased) {
            $this->_objectCopyService->copyFieldsetToTarget(
                \downloadable_sales_copy_order::class,
                'to_downloadable',
                $order,
                $linkPurchased
            );
            $linkPurchased->save();
        }

        return $this;
    }

    /**
     * @return \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Collection
     */
    protected function _createPurchasedCollection()
    {
        return $this->_purchasedFactory->create();
    }
}
