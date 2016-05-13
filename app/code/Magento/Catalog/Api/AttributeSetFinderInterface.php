<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

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
