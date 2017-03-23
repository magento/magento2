<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
