<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer\Attribute\Source;

/**
 * Customer store attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Store extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Store\Model\System\Store
     * @since 2.0.0
     */
    protected $_store;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     * @since 2.0.0
     */
    protected $_storesFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Store\Model\System\Store $store
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storesFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Store\Model\System\Store $store,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storesFactory
    ) {
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
        $this->_store = $store;
        $this->_storesFactory = $storesFactory;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $collection = $this->_createStoresCollection();
            if ('store_id' == $this->getAttribute()->getAttributeCode()) {
                $collection->setWithoutDefaultFilter();
            }
            $this->_options = $this->_store->getStoreValuesForForm();
            if ('created_in' == $this->getAttribute()->getAttributeCode()) {
                array_unshift($this->_options, ['value' => '0', 'label' => __('Admin')]);
            }
        }
        return $this->_options;
    }

    /**
     * @param string $value
     * @return array|string
     * @since 2.0.0
     */
    public function getOptionText($value)
    {
        if (!$value) {
            $value = '0';
        }
        $isMultiple = false;
        if (strpos($value, ',')) {
            $isMultiple = true;
            $value = explode(',', $value);
        }

        if (!$this->_options) {
            $collection = $this->_createStoresCollection();
            if ('store_id' == $this->getAttribute()->getAttributeCode()) {
                $collection->setWithoutDefaultFilter();
            }
            $this->_options = $collection->load()->toOptionArray();
            if ('created_in' == $this->getAttribute()->getAttributeCode()) {
                array_unshift($this->_options, ['value' => '0', 'label' => __('Admin')]);
            }
        }

        if ($isMultiple) {
            $values = [];
            foreach ($value as $val) {
                $values[] = $this->_options[$val];
            }
            return $values;
        } else {
            return $this->_options[$value];
        }
    }

    /**
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     * @since 2.0.0
     */
    protected function _createStoresCollection()
    {
        return $this->_storesFactory->create();
    }
}
