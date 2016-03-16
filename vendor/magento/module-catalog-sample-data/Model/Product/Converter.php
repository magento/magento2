<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSampleData\Model\Product;

class Converter
{
    /**
     * @var \Magento\Catalog\Model\Category\Tree
     */
    protected $categoryTree;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $attrOptionCollectionFactory;

    /**
     * @var array
     */
    protected $attributeCodeOptionsPair;

    /**
     * @var array
     */
    protected $attributeCodeOptionValueIdsPair;

    /**
     * @var int
     */
    protected $attributeSetId;

    /**
     * @var array
     */
    protected $loadedAttributeSets;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @var array
     */
    protected $productIds;

    /**
     * @param \Magento\Catalog\Model\Category\TreeFactory $categoryTreeFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryResourceTreeFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Category\TreeFactory $categoryTreeFactory,
        \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryResourceTreeFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
         $this->categoryTree = $categoryTreeFactory->create(
            [
                'categoryTree' => $categoryResourceTreeFactory->create(),
                'categoryCollection' => $categoryCollectionFactory->create()
            ]
        );
        $this->eavConfig = $eavConfig;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->productCollection = $productCollectionFactory->create();
    }

    /**
     * Convert CSV format row to array
     *
     * @param array $row
     * @return array
     */
    public function convertRow($row)
    {
        $data = [];
        foreach ($row as $field => $value) {
            if ('category' == $field) {
                $data['category_ids'] = $this->getCategoryIds($this->getArrayValue($value));
                continue;
            }

            if ('qty' == $field) {
                $data['quantity_and_stock_status'] = ['qty' => $value];
                continue;
            }

            $convertedField = $this->convertField($data, $field, $value);
            if ($convertedField) {
                continue;
            }

            $options = $this->getAttributeOptionValueIdsPair($field);
            if ($options) {
                $value = $this->setOptionsToValues($options, $value);
            }
            $data[$field] = $value;
        }
        return $data;
    }

    /**
     * Convert field
     *
     * @param array $data
     * @param string $field
     * @param string $value
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function convertField(&$data, $field, $value)
    {
        return false;
    }

    /**
     * Assign options data to attribute value
     *
     * @param mixed $options
     * @param array $values
     * @return array|mixed
     */
    protected function setOptionsToValues($options, $values)
    {
        $values = $this->getArrayValue($values);
        $result = [];
        foreach ($values as $value) {
            if (isset($options[$value])) {
                $result[] = $options[$value];
            }
        }
        return count($result) == 1 ? current($result) : $result;
    }

    /**
     * Get formatted array value
     *
     * @param mixed $value
     * @return array
     */
    protected function getArrayValue($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if (false !== strpos($value, "\n")) {
            $value = array_filter(explode("\n", $value));
        }
        return !is_array($value) ? [$value] : $value;
    }

    /**
     * Get product category ids from array
     *
     * @param array $categories
     * @return array
     */
    protected function getCategoryIds($categories)
    {
        $ids = [];
        $tree = $this->categoryTree->getTree($this->categoryTree->getRootNode(null), null);
        foreach ($categories as $name) {
            foreach ($tree->getChildrenData() as $child) {
                if ($child->getName() == $name) {
                    /** @var \Magento\Catalog\Api\Data\CategoryTreeInterface $child */
                    $tree = $child;
                    $ids[] = $child->getId();
                    if (!$tree->getChildrenData()) {
                        $tree = $this->categoryTree->getTree($this->categoryTree->getRootNode(null), null);
                    }
                    break;
                }
            }
        }
        return $ids;
    }

    /**
     * Get attribute options by attribute code
     *
     * @param string $attributeCode
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection|null
     */
    public function getAttributeOptions($attributeCode)
    {
        if (!isset($this->attributeCodeOptionsPair[$attributeCode])) {
            $this->loadAttributeOptions();
        }
        return isset($this->attributeCodeOptionsPair[$attributeCode])
            ? $this->attributeCodeOptionsPair[$attributeCode]
            : null;
    }

    /**
     * Loads all attributes with options for current attribute set
     *
     * @return $this
     */
    protected function loadAttributeOptions()
    {
        $attributeSetIdCache = $this->getAttributeSetId();
        if (empty($attributeSetIdCache)) {
            $attributeSetIdCache = 0;
        }
        if (isset($this->loadedAttributeSets[$attributeSetIdCache])) {
            return $this;
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collection */
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToSelect(['attribute_code', 'attribute_id']);
        $collection->setAttributeSetFilter($this->getAttributeSetId());
        $collection->setFrontendInputTypeFilter(['in' => ['select', 'multiselect']]);
        foreach ($collection as $item) {
            $options = $this->attrOptionCollectionFactory->create()
                ->setAttributeFilter($item->getAttributeId())->setPositionOrder('asc', true)->load();
            $this->attributeCodeOptionsPair[$item->getAttributeCode()] = $options;
        }
        $this->loadedAttributeSets[$attributeSetIdCache] = true;
        return $this;
    }

    /**
     * Find attribute option value pair
     *
     * @param mixed $attributeCode
     * @return mixed
     */
    protected function getAttributeOptionValueIdsPair($attributeCode)
    {
        if (!empty($this->attributeCodeOptionValueIdsPair[$attributeCode])) {
            return $this->attributeCodeOptionValueIdsPair[$attributeCode];
        }

        $options = $this->getAttributeOptions($attributeCode);
        $opt = [];
        if ($options) {
            foreach ($options as $option) {
                $opt[$option->getValue()] = $option->getId();
            }
        }
        $this->attributeCodeOptionValueIdsPair[$attributeCode] = $opt;
        return $this->attributeCodeOptionValueIdsPair[$attributeCode];
    }

    /**
     * @return int
     */
    protected function getAttributeSetId()
    {
        return $this->attributeSetId;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setAttributeSetId($value)
    {
        if ($this->attributeSetId != $value) {
            $this->loadAttributeOptions();
        }
        $this->attributeSetId = $value;
        return $this;
    }

    /**
     * Retrieve product ID by sku
     *
     * @param string $sku
     * @return int|null
     */
    protected function getProductIdBySku($sku)
    {
        if (empty($this->productIds)) {
            $this->productCollection->addAttributeToSelect('sku');
            foreach ($this->productCollection as $product) {
                $this->productIds[$product->getSku()] = $product->getId();
            }
        }
        if (isset($this->productIds[$sku])) {
            return $this->productIds[$sku];
        }
        return null;
    }
}
