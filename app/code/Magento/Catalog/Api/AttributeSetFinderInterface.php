<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

/**
 * Interface AttributeSetFinderInterface
 * @api
 */
interface AttributeSetFinderInterface
{
    /**
     * Get attribute set ids by product ids
     *
     * @param array $productIds
     * @return array
     */
    public function findAttributeSetIdsByProductIds(array $productIds);
}
