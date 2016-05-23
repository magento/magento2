<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Search;

/**
 * Search Order Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Order extends \Magento\Framework\DataObject
{
    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Data $adminhtmlData
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_adminhtmlData = $adminhtmlData;
    }

    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $result = [];
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($result);
            return $this;
        }

        $query = $this->getQuery();
        //TODO: add full name logic
        $collection = $this->_collectionFactory->create()->addAttributeToSelect(
            '*'
        )->addAttributeToSearchFilter(
            [
                ['attribute' => 'increment_id', 'like' => $query . '%'],
                ['attribute' => 'billing_firstname', 'like' => $query . '%'],
                ['attribute' => 'billing_lastname', 'like' => $query . '%'],
                ['attribute' => 'billing_telephone', 'like' => $query . '%'],
                ['attribute' => 'billing_postcode', 'like' => $query . '%'],
                ['attribute' => 'shipping_firstname', 'like' => $query . '%'],
                ['attribute' => 'shipping_lastname', 'like' => $query . '%'],
                ['attribute' => 'shipping_telephone', 'like' => $query . '%'],
                ['attribute' => 'shipping_postcode', 'like' => $query . '%'],
            ]
        )->setCurPage(
            $this->getStart()
        )->setPageSize(
            $this->getLimit()
        )->load();

        foreach ($collection as $order) {
            $result[] = [
                'id' => 'order/1/' . $order->getId(),
                'type' => __('Order'),
                'name' => __('Order #%1', $order->getIncrementId()),
                'description' => $order->getFirstname() . ' ' . $order->getLastname(),
                'url' => $this->_adminhtmlData->getUrl('sales/order/view', ['order_id' => $order->getId()]),
            ];
        }

        $this->setResults($result);

        return $this;
    }
}
