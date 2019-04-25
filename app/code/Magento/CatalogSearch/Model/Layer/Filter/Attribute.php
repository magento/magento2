<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

/**
 * Layer attribute filter
 */
class Attribute extends AbstractFilter
{
    /**
     * @var \Magento\Framework\Filter\StripTags
     */
    private $tagFilter;

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Filter\StripTags $tagFilter
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->tagFilter = $tagFilter;
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);
        if (empty($attributeValue) && !is_numeric($attributeValue)) {
            return $this;
        }
        $attribute = $this->getAttributeModel();
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();
        $productCollection->addFieldToFilter($attribute->getAttributeCode(), $attributeValue);
        $label = $this->getOptionText($attributeValue);
        $this->getLayer()
            ->getState()
            ->addFilter($this->_createItem($label, $attributeValue));

        $this->setItems([]); // set items to disable show filtering
        return $this;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();
        $optionsFacetedData = $productCollection->getFacetedData($attribute->getAttributeCode());

        $isAttributeFilterable =
            $this->getAttributeIsFilterable($attribute) === static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS;

        if (count($optionsFacetedData) === 0 && !$isAttributeFilterable) {
            return $this->itemDataBuilder->build();
        }

        $options = $attribute->getFrontend()
            ->getSelectOptions();
        foreach ($options as $option) {
            $this->buildOptionData($option, $isAttributeFilterable, $optionsFacetedData);
        }

        return $this->itemDataBuilder->build();
    }

    /**
     * Build option data
     *
     * @param array $option
     * @param boolean $isAttributeFilterable
     * @param array $optionsFacetedData
     * @return void
     */
    private function buildOptionData($option, $isAttributeFilterable, $optionsFacetedData)
    {
        $value = $this->getOptionValue($option);
        if ($value === false) {
            return;
        }
        $count = $this->getOptionCount($value, $optionsFacetedData);
        if ($isAttributeFilterable && $count === 0) {
            return;
        }

        $this->itemDataBuilder->addItemData(
            $this->tagFilter->filter($option['label']),
            $value,
            $count
        );
    }

    /**
     * Retrieve option value if it exists
     *
     * @param array $option
     * @return bool|string
     */
    private function getOptionValue($option)
    {
        if (empty($option['value']) && !is_numeric($option['value'])) {
            return false;
        }
        return $option['value'];
    }

    /**
     * Retrieve count of the options
     *
     * @param int|string $value
     * @param array $optionsFacetedData
     * @return int
     */
    private function getOptionCount($value, $optionsFacetedData)
    {
        return isset($optionsFacetedData[$value]['count'])
            ? (int)$optionsFacetedData[$value]['count']
            : 0;
    }

    /**
     * @inheritdoc
     */
    protected function isOptionReducesResults($optionCount, $totalSize)
    {
        return $optionCount <= $totalSize;
    }
}
