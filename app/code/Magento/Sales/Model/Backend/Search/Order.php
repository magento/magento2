<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Backend\Search;

use Magento\Backend\Api\Search\ItemsInterface;
use Magento\Backend\Model\Search\ItemsAbstract;

/**
 * Search Order Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 */
class Order extends ItemsAbstract implements ItemsInterface
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
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Data $adminhtmlData,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_adminhtmlData = $adminhtmlData;
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $result = [];
        if (!$this->hasData(self::START) || !$this->hasData(self::LIMIT) || !$this->hasData(self::QUERY)) {
            return $result;
        }

        $query = $this->getData(self::QUERY);
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
            $this->getData(self::START)
        )->setPageSize(
            $this->getData(self::LIMIT)
        )->load();

        foreach ($collection as $order) {
            /** @var \Magento\Sales\Model\Order $order */
            $result[] = [
                'id' => 'order/1/' . $order->getId(),
                'type' => __('Order'),
                'name' => __('Order #%1', $order->getIncrementId()),
                'description' => $order->getFirstname() . ' ' . $order->getLastname(),
                'url' => $this->_adminhtmlData->getUrl('sales/order/view', ['order_id' => $order->getId()]),
            ];
        }
        return $result;
    }
}
