<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @throws \Magento\Framework\Model\Exception
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);
        if (empty($attributeValue)) {
            return $this;
        }
        $attribute = $this->getAttributeModel();
        /** @var \Magento\CatalogSearch\Model\Resource\Fulltext\Collection $productCollection */
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
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        /** @var \Magento\CatalogSearch\Model\Resource\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();
        $optionsFacetedData = $productCollection->getFacetedData($attribute->getAttributeCode());

        $productSize = $productCollection->getSize();

        $options = $attribute->getFrontend()
            ->getSelectOptions();
        foreach ($options as $option) {
            if (empty($option['value'])) {
                continue;
            }
            // Check filter type
            if (empty($optionsFacetedData[$option['value']]['count'])
                || ($this->getAttributeIsFilterable($attribute) == static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS
                    && !$this->isOptionReducesResults($optionsFacetedData[$option['value']]['count'], $productSize))
            ) {
                continue;
            }
            $this->itemDataBuilder->addItemData(
                $this->tagFilter->filter($option['label']),
                $option['value'],
                $optionsFacetedData[$option['value']]['count']
            );
        }

        return $this->itemDataBuilder->build();
    }
}
