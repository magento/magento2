<?php
/**
 * Plugin for \Magento\Catalog\Model\Product\Attribute\Repository
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Plugin;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Catalog\Model\FilterProductCustomAttribute as Filter;

class FilterCustomAttribute
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @param Filter $filter
     * @internal param Filter $customAttribute
     */
    public function __construct(Filter $filter)
    {
        $this->filter = $filter;
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
        $return = [];
        foreach ($attributes as $attribute) {
            $return[$attribute->getAttributeCode()] = $attribute;
        }

        return $this->filter->execute($return);
    }
}
