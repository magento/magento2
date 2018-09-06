<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search;

use Magento\Framework\Search\Request\FilterInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Catalog\Model\Product;

/**
 * Class CustomAttributeFilterSelector
 * Checks if FilterInterface is by custom attribute
 *
 * @deprecated CatalogSearch will be removed in 2.4, and {@see \Magento\ElasticSearch}
 *             will replace it as the default search engine.
 */
class CustomAttributeFilterCheck
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @param EavConfig $eavConfig
     */
    public function __construct(
        EavConfig $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Checks if FilterInterface is by custom attribute
     *
     * @param FilterInterface $filter
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isCustom(FilterInterface $filter)
    {
        $attribute = $this->getAttributeByCode($filter->getField());

        return $attribute
            && $filter->getType() === FilterInterface::TYPE_TERM
            && in_array($attribute->getFrontendInput(), ['select', 'multiselect'], true);
    }

    /**
     * Return attribute by its code
     *
     * @param string $field
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeByCode($field)
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, $field);
    }
}
