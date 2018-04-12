<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\ConditionBuilder;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Class ConditionBuilderFactory
 * Creates appropriate condition builder based on filter field
 * - native attribute condition builder if filer field is native attribute in product
 * - eav condition builder if filer field is eav attribute
 *
 * @package Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\ConditionBuilder
 */
class ConditionBuilderFactory
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * @var CustomConditionInterface
     */
    private $eavAttrConditionBuilder;

    /**
     * @var CustomConditionInterface
     */
    private $nativeAttrConditionBuilder;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param CustomConditionInterface $eavAttrConditionBuilder
     * @param CustomConditionInterface $nativeAttrConditionBuilder
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        CustomConditionInterface $eavAttrConditionBuilder,
        CustomConditionInterface $nativeAttrConditionBuilder
    ) {
        $this->eavConfig = $eavConfig;
        $this->productResource = $productResource;
        $this->eavAttrConditionBuilder = $eavAttrConditionBuilder;
        $this->nativeAttrConditionBuilder = $nativeAttrConditionBuilder;
    }

    /**
     * @param Filter $filter
     * @return CustomConditionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createByFilter(Filter $filter): CustomConditionInterface
    {
        $attribute = $this->getAttributeByCode($filter->getField());

        if ($attribute->getBackendTable() === $this->productResource->getEntityTable()) {
            return $this->nativeAttrConditionBuilder;
        }

        return $this->eavAttrConditionBuilder;
    }

    /**
     * @param string $field
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeByCode(string $field): Attribute
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
    }
}
