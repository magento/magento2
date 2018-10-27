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
 * Creates appropriate condition builder based on filter field
 * - native attribute condition builder if filter field is native attribute in product
 * - eav condition builder if filter field is eav attribute
 */
class Factory
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
    private $eavAttributeConditionBuilder;

    /**
     * @var CustomConditionInterface
     */
    private $nativeAttributeConditionBuilder;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param CustomConditionInterface $eavAttributeConditionBuilder
     * @param CustomConditionInterface $nativeAttributeConditionBuilder
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        CustomConditionInterface $eavAttributeConditionBuilder,
        CustomConditionInterface $nativeAttributeConditionBuilder
    ) {
        $this->eavConfig = $eavConfig;
        $this->productResource = $productResource;
        $this->eavAttributeConditionBuilder = $eavAttributeConditionBuilder;
        $this->nativeAttributeConditionBuilder = $nativeAttributeConditionBuilder;
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
            return $this->nativeAttributeConditionBuilder;
        }

        return $this->eavAttributeConditionBuilder;
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
