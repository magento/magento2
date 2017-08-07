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
 * @since 2.2.0
 */
class CustomAttributeFilterCheck
{
    /**
     * @var EavConfig
     * @since 2.2.0
     */
    private $eavConfig;

    /**
     * @param EavConfig $eavConfig
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    private function getAttributeByCode($field)
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, $field);
    }
}
