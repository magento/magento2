<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source;

/**
 * @api
 * @since 2.0.0
 */
class Store implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_options;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     * @since 2.0.0
     */
    protected $_storesFactory;

    /**
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storesFactory
     * @since 2.0.0
     */
    public function __construct(\Magento\Store\Model\ResourceModel\Store\CollectionFactory $storesFactory)
    {
        $this->_storesFactory = $storesFactory;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            /** @var $stores \Magento\Store\Model\ResourceModel\Store\Collection */
            $stores = $this->_storesFactory->create();
            $this->_options = $stores->load()->toOptionArray();
        }
        return $this->_options;
    }
}
