<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options;

use \Magento\Swatches\Model\Swatch as SwatchModel;

/**
 * Backend swatch abstract block
 */
abstract class AbstractSwatch extends \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options
{
    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * Helper to move image from tmp to catalog
     *
     * @var \Magento\Swatches\Helper\Media
     */
    protected $swatchHelper;

    /**
     * Prepare option values of user defined attribute
     *
     * @codeCoverageIgnore
     * @param array|\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option $option
     * @param string $inputType
     * @param array $defaultValues
     * @return array
     */
    protected function _prepareUserDefinedAttributeOptionValues($option, $inputType, $defaultValues)
    {
        $optionId = $option->getId();

        $value['checked'] = in_array($optionId, $defaultValues) ? 'checked="checked"' : '';
        $value['intype'] = $inputType;
        $value['id'] = $optionId;
        $value['sort_order'] = $option->getSortOrder();

        foreach ($this->getStores() as $store) {
            $value = array_merge(
                $value,
                $this->createStoreValues($store->getId(), $optionId)
            );
        }

        return [$value];
    }

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Swatches\Helper\Media $swatchHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Swatches\Helper\Media $swatchHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $attrOptionCollectionFactory, $universalFactory, $data);
        $this->swatchHelper = $swatchHelper;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * Create store values
     *
     * Method not intended to escape HTML entities
     * Escaping will be applied in template files
     *
     * @param integer $storeId
     * @param integer $optionId
     * @return array
     */
    protected function createStoreValues($storeId, $optionId)
    {
        $value = [];
        $storeValues = $this->getStoreOptionValues($storeId);
        $swatchStoreValue = isset($storeValues['swatch']) ? $storeValues['swatch'] : null;
        $value['store' . $storeId] = isset($storeValues[$optionId]) ? $storeValues[$optionId] : '';
        $value['swatch' . $storeId] = isset($swatchStoreValue[$optionId]) ? $swatchStoreValue[$optionId] : '';

        return $value;
    }

    /**
     * Retrieve attribute option values for given store id
     *
     * @param int $storeId
     * @return array
     */
    public function getStoreOptionValues($storeId)
    {
        $values = $this->getData('store_option_values_' . $storeId);
        if ($values === null) {
            $values = [];
            $valuesCollection = $this->_attrOptionCollectionFactory->create();
            $valuesCollection->setAttributeFilter(
                $this->getAttributeObject()->getId()
            );
            $this->addCollectionStoreFilter($valuesCollection, $storeId);
            $valuesCollection->getSelect()->joinLeft(
                ['swatch_table' => $valuesCollection->getTable('eav_attribute_option_swatch')],
                'swatch_table.option_id = main_table.option_id AND swatch_table.store_id = '.$storeId,
                'swatch_table.value AS label'
            );
            $valuesCollection->load();
            foreach ($valuesCollection as $item) {
                $values[$item->getId()] = $item->getValue();
                $values['swatch'][$item->getId()] = $item->getLabel();
            }
            $this->setData('store_option_values_' . $storeId, $values);
        }
        return $values;
    }

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $valuesCollection
     * @param int $storeId
     * @return void
     */
    private function addCollectionStoreFilter($valuesCollection, $storeId)
    {
        $joinCondition = $valuesCollection->getConnection()->quoteInto(
            'tsv.option_id = main_table.option_id AND tsv.store_id = ?',
            $storeId
        );

        $select = $valuesCollection->getSelect();
        $select->joinLeft(
            ['tsv' => $valuesCollection->getTable('eav_attribute_option_value')],
            $joinCondition,
            'value'
        );
        if (\Magento\Store\Model\Store::DEFAULT_STORE_ID == $storeId) {
            $select->where(
                'tsv.store_id = ?',
                $storeId
            );
        }
        $valuesCollection->setOrder('value', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
    }
}
