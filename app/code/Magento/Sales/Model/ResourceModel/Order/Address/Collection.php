<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Address;

use Magento\Sales\Api\Data\OrderAddressSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\ResourceModel\Order\Address as AddressResource;

/**
 * Order addresses collection
 */
class Collection extends AbstractCollection implements OrderAddressSearchResultInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_address_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'order_address_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Address::class,
            AddressResource::class
        );
    }
}
