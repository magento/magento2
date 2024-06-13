<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Eav\Model\ResourceModel\Attribute\DefaultEntityAttributes\ProviderInterface;

/**
 * Product default attributes provider
 *
 * @codeCoverageIgnore
 */
class DefaultAttributes implements ProviderInterface
{
    /**
     * Retrieve default entity static attributes
     *
     * @return string[]
     */
    public function getDefaultAttributes()
    {
        return ['entity_id', 'attribute_set_id', 'type_id', 'created_at', 'updated_at', 'sku'];
    }
}
