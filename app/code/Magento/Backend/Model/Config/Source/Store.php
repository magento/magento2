<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Model\Config\Source;

class Store implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var \Magento\Store\Model\Resource\Store\CollectionFactory
     */
    protected $_storesFactory;

    /**
     * @param \Magento\Store\Model\Resource\Store\CollectionFactory $storesFactory
     */
    public function __construct(\Magento\Store\Model\Resource\Store\CollectionFactory $storesFactory)
    {
        $this->_storesFactory = $storesFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            /** @var $stores \Magento\Store\Model\Resource\Store\Collection */
            $stores = $this->_storesFactory->create();
            $this->_options = $stores->load()->toOptionArray();
        }
        return $this->_options;
    }
}
