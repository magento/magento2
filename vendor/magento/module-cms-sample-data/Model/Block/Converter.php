<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CmsSampleData\Model\Block;

/**
 * Class Converter
 */
class Converter
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\CatalogSampleData\Model\Product\Converter
     */
    protected $productConverter;

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
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\CatalogSampleData\Model\Product\Converter $productConverter
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\CatalogSampleData\Model\Product\Converter $productConverter,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->eavConfig = $eavConfig;
        $this->productConverter = $productConverter;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
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
            if ('content' == $field) {
                $data['block'][$field] = $this->replaceMatches($value);
                continue;
            }
            $data['block'][$field] = $value;
        }
        return $data;
    }

    /**
     * @param string $urlKey
     * @return \Magento\Framework\Object
     */
    protected function getCategoryByUrlKey($urlKey)
    {
        $category = $this->categoryFactory->create()
            ->addAttributeToFilter('url_key', $urlKey)
            ->addUrlRewriteToResult()
            ->getFirstItem();
        return $category;
    }

    /**
     * Get formatted array value
     *
     * @param mixed $value
     * @param string $separator
     * @return array
     */
    protected function getArrayValue($value, $separator = "/")
    {
        if (is_array($value)) {
            return $value;
        }
        if (false !== strpos($value, $separator)) {
            $value = array_filter(explode($separator, $value));
        }
        return !is_array($value) ? [$value] : $value;
    }

    /**
     * @param string $content
     * @return mixed
     */
    protected function replaceMatches($content)
    {
        $matches = $this->getMatches($content);
        if (!empty($matches['value'])) {
            $replaces = $this->getReplaces($matches);
            $content = preg_replace($replaces['regexp'], $replaces['value'], $content);
        }
        return $content;
    }

    /**
     * @param string $content
     * @return array
     */
    protected function getMatches($content)
    {
        $regexp = '/{{(category[^ ]*) key="([^"]+)"}}/';
        preg_match_all($regexp, $content, $matchesCategory);
        $regexp = '/{{(product[^ ]*) sku="([^"]+)"}}/';
        preg_match_all($regexp, $content, $matchesProduct);
        $regexp = '/{{(attribute) key="([^"]*)"}}/';
        preg_match_all($regexp, $content, $matchesAttribute);
        return [
            'type' => $matchesCategory[1] + $matchesAttribute[1] + $matchesProduct[1],
            'value' => $matchesCategory[2] + $matchesAttribute[2] + $matchesProduct[2]
        ];
    }

    /**
     * @param array $matches
     * @return array
     */
    protected function getReplaces($matches)
    {
        $replaceData = [];

        foreach ($matches['value'] as $matchKey => $matchValue) {
            $callback = "matcher" . ucfirst(trim($matches['type'][$matchKey]));
            $matchResult = call_user_func_array([$this, $callback], [$matchValue]);
            if (!empty($matchResult)) {
                $replaceData = array_merge_recursive($replaceData, $matchResult);
            }
        }
        return $replaceData;
    }

    /**
     * @param string $urlAttributes
     * @return string
     */
    protected function getUrlFilter($urlAttributes)
    {
        $separatedAttributes = $this->getArrayValue($urlAttributes, ';');
        $urlFilter = null;
        foreach ($separatedAttributes as $attributeNumber => $attributeValue) {
            $attributeData = $this->getArrayValue($attributeValue, '=');
            $attributeOptions = $this->productConverter->getAttributeOptions($attributeData[0]);
            $attributeValue = $attributeOptions->getItemByColumnValue('value', $attributeData[1]);
            if ($attributeNumber == 0) {
                $urlFilter = $attributeData[0] . '=' . $attributeValue->getId();
                continue;
            }
            $urlFilter .= '&' . $attributeData[0] . '=' . $attributeValue->getId();
        }
        return $urlFilter;
    }

    /**
     * Get attribute options by attribute code
     *
     * @param string $attributeCode
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection|null
     */
    protected function getAttributeOptions($attributeCode)
    {
        if (!$this->attributeCodeOptionsPair || !isset($this->attributeCodeOptionsPair[$attributeCode])) {
            $this->loadAttributeOptions($attributeCode);
        }
        return isset($this->attributeCodeOptionsPair[$attributeCode])
            ? $this->attributeCodeOptionsPair[$attributeCode]
            : null;
    }

    /**
     * Loads all attributes with options for attribute
     *
     * @param string $attributeCode
     * @return $this
     */
    protected function loadAttributeOptions($attributeCode)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collection */
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToSelect(['attribute_code', 'attribute_id']);
        $collection->addFieldToFilter('attribute_code', $attributeCode);
        $collection->setFrontendInputTypeFilter(['in' => ['select', 'multiselect']]);
        foreach ($collection as $item) {
            $options = $this->attrOptionCollectionFactory->create()
                ->setAttributeFilter($item->getAttributeId())->setPositionOrder('asc', true)->load();
            $this->attributeCodeOptionsPair[$item->getAttributeCode()] = $options;
        }
        return $this;
    }

    /**
     * Find attribute option value pair
     *
     * @param string $attributeCode
     * @param string $value
     * @return mixed
     */
    protected function getAttributeOptionValueId($attributeCode, $value)
    {
        if (!empty($this->attributeCodeOptionValueIdsPair[$attributeCode][$value])) {
            return $this->attributeCodeOptionValueIdsPair[$attributeCode][$value];
        }

        $options = $this->getAttributeOptions($attributeCode);
        $opt = [];
        if ($options) {
            foreach ($options as $option) {
                $opt[$option->getValue()] = $option->getId();
            }
        }
        $this->attributeCodeOptionValueIdsPair[$attributeCode] = $opt;
        return $this->attributeCodeOptionValueIdsPair[$attributeCode][$value];
    }

    /**
     * @param string $matchValue
     * @return array
     */
    protected function matcherCategory($matchValue)
    {
        $replaceData = [];
        $category = $this->getCategoryByUrlKey($matchValue);
        if (!empty($category)) {
            $categoryUrl = $category->getRequestPath();
            $replaceData['regexp'][] = '/{{category key="' . $matchValue . '"}}/';
            $replaceData['value'][] = '{{store url=""}}' . $categoryUrl;
        }
        return $replaceData;
    }

    /**
     * @param string $matchValue
     * @return array
     */
    protected function matcherCategoryId($matchValue)
    {
        $replaceData = [];
        $category = $this->getCategoryByUrlKey($matchValue);
        if (!empty($category)) {
            $replaceData['regexp'][] = '/{{categoryId key="' . $matchValue . '"}}/';
            $replaceData['value'][] = sprintf('%03d', $category->getId());
        }
        return $replaceData;
    }

    /**
     * @param string $matchValue
     * @return array
     */
    protected function matcherProduct($matchValue)
    {
        $replaceData = [];
        $productCollection = $this->productCollectionFactory->create();
        $productItem = $productCollection->addAttributeToFilter('sku', $matchValue)
            ->addUrlRewrite()
            ->getFirstItem();
        $productUrl = null;
        if ($productItem) {
            $productUrl = '{{store url=""}}' .  $productItem->getRequestPath();
        }
        $replaceData['regexp'][] = '/{{product sku="' . $matchValue . '"}}/';
        $replaceData['value'][] = $productUrl;
        return $replaceData;
    }

    /**
     * @param string $matchValue
     * @return array
     */
    protected function matcherAttribute($matchValue)
    {
        $replaceData = [];
        if (strpos($matchValue, ':') === false) {
            return $replaceData;
        }
        list($code, $value) = explode(':', $matchValue);

        if (!empty($code) && !empty($value)) {
            $replaceData['regexp'][] = '/{{attribute key="' . $matchValue . '"}}/';
            $replaceData['value'][] = sprintf('%03d', $this->getAttributeOptionValueId($code, $value));
        }
        return $replaceData;
    }
}
