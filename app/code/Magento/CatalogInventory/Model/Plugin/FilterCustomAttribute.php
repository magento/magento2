<?php
/**
 * Plugin for \Magento\Catalog\Model\Product\Attribute\Repository
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Repository;

class FilterCustomAttribute
{
    /**
     * @var array
     */
    private $blackList;

    /**
     * @param array $blackList
     */
    public function __construct(array $blackList = [])
    {
        $this->blackList = $blackList;
    }

    /**
     * Remove attributes from black list
     *
     * @param Repository $repository
     * @param array $attributes
     * @return \Magento\Framework\Api\MetadataObjectInterface[]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCustomAttributesMetadata(Repository $repository, array $attributes): array
    {
        return $this->filterAttributes($attributes);
    }

    /**
     * Remove attributes from black list
     *
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param array $attributes
     * @param string $entityType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetEntityAttributes(\Magento\Eav\Model\Config $eavConfig, $attributes, $entityType): array
    {
        $entityType = $eavConfig->getEntityType($entityType);
        if ($entityType->getEntityTypeCode() === Product::ENTITY) {
            $attributes = $this->filterAttributes($attributes);
        }

        return $attributes;
    }

    /**
     * @param array $attributes
     * @return array
     */
    private function filterAttributes(array $attributes): array
    {
        foreach ($attributes as $key => $attribute) {
            if (in_array($attribute->getAttributeCode(), $this->blackList)) {
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }
}
