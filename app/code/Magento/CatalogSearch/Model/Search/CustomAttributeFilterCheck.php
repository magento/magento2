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
 * Checks if FilterInterface is by custom attribute
 *
 * @deprecated
 * @see \Magento\ElasticSearch
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
            && in_array($attribute->getFrontendInput(), ['select', 'multiselect', 'boolean'], true);
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
