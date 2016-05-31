<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class SetLinkStatusObserver implements ObserverInterface
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory
     */
    protected $_itemsFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_itemsFactory = $itemsFactory;
    }


    /**
     * Set status of link
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order->getId()) {
            //order not saved in the database
            return $this;
        }

        /* @var $order \Magento\Sales\Model\Order */
        $status = '';
        $linkStatuses = [
            'pending' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING,
            'expired' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED,
            'avail' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_AVAILABLE,
            'payment_pending' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING_PAYMENT,
            'payment_review' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PAYMENT_REVIEW,
        ];

        $downloadableItemsStatuses = [];
        $orderItemStatusToEnable = $this->_scopeConfig->getValue(
            \Magento\Downloadable\Model\Link\Purchased\Item::XML_PATH_ORDER_ITEM_STATUS,
            ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        );

        if ($order->getState() == \Magento\Sales\Model\Order::STATE_HOLDED) {
            $status = $linkStatuses['pending'];
        } elseif ($order->isCanceled()
            || $order->getState() == \Magento\Sales\Model\Order::STATE_CLOSED
            || $order->getState() == \Magento\Sales\Model\Order::STATE_COMPLETE
        ) {
            $expiredStatuses = [
                \Magento\Sales\Model\Order\Item::STATUS_CANCELED,
                \Magento\Sales\Model\Order\Item::STATUS_REFUNDED,
            ];
            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                    || $item->getRealProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ) {
                    if (in_array($item->getStatusId(), $expiredStatuses)) {
                        $downloadableItemsStatuses[$item->getId()] = $linkStatuses['expired'];
                    } else {
                        $downloadableItemsStatuses[$item->getId()] = $linkStatuses['avail'];
                    }
                }
            }
        } elseif ($order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
            $status = $linkStatuses['payment_pending'];
        } elseif ($order->getState() == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW) {
            $status = $linkStatuses['payment_review'];
        } else {
            $availableStatuses = [$orderItemStatusToEnable, \Magento\Sales\Model\Order\Item::STATUS_INVOICED];
            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                    || $item->getRealProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ) {
                    if ($item->getStatusId() == \Magento\Sales\Model\Order\Item::STATUS_BACKORDERED
                        && $orderItemStatusToEnable == \Magento\Sales\Model\Order\Item::STATUS_PENDING
                        && !in_array(
                            \Magento\Sales\Model\Order\Item::STATUS_BACKORDERED,
                            $availableStatuses,
                            true
                        )
                    ) {
                        $availableStatuses[] = \Magento\Sales\Model\Order\Item::STATUS_BACKORDERED;
                    }

                    if (in_array($item->getStatusId(), $availableStatuses)) {
                        $downloadableItemsStatuses[$item->getId()] = $linkStatuses['avail'];
                    }
                }
            }
        }
        if (!$downloadableItemsStatuses && $status) {
            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                    || $item->getRealProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ) {
                    $downloadableItemsStatuses[$item->getId()] = $status;
                }
            }
        }

        if ($downloadableItemsStatuses) {
            $linkPurchased = $this->_createItemsCollection()->addFieldToFilter(
                'order_item_id',
                ['in' => array_keys($downloadableItemsStatuses)]
            );
            foreach ($linkPurchased as $link) {
                if ($link->getStatus() != $linkStatuses['expired']
                    && !empty($downloadableItemsStatuses[$link->getOrderItemId()])
                ) {
                    $link->setStatus($downloadableItemsStatuses[$link->getOrderItemId()])->save();
                }
            }
        }

        return $this;
    }

    /**
     * @return \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection
     */
    protected function _createItemsCollection()
    {
        return $this->_itemsFactory->create();
    }
}
