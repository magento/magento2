<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api;

/**
 * Interface AttributeSetFinderInterface
 * @api
 * @since 101.0.0
 */
interface AttributeSetFinderInterface
{
    /**
     * Get attribute set ids by product ids
     *
     * @param array $productIds
     * @return array
     * @since 101.0.0
     */
    public function findAttributeSetIdsByProductIds(array $productIds);
}
