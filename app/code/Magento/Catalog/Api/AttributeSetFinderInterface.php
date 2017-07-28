<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

/**
 * Interface AttributeSetFinderInterface
 * @api
 * @since 2.1.0
 */
interface AttributeSetFinderInterface
{
    /**
     * Get attribute set ids by product ids
     *
     * @param array $productIds
     * @return array
     * @since 2.1.0
     */
    public function findAttributeSetIdsByProductIds(array $productIds);
}
