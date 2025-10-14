<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer\Attribute\Source;

/**
 * Customer store attribute source
 */
class Store extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_store;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    protected $_storesFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Store\Model\System\Store $store
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storesFactory
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
     * @inheritdoc
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
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
     * Return option text
     *
     * @param string $value
     * @return array|string
     */
    public function getOptionText($value)
    {
        if (!$value) {
            $value = '0';
        }
        $isMultiple = false;
        if (strpos($value, ',') !== false) {
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
     * Return new stores collection
     *
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    protected function _createStoresCollection()
    {
        return $this->_storesFactory->create();
    }
}
