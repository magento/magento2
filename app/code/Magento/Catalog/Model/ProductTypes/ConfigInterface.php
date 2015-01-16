<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ProductTypes;

interface ConfigInterface
{
    /**
     * Get configuration of product type by name
     *
     * @param string $name
     * @return array
     */
    public function getType($name);

    /**
     * Get configuration of all registered product types
     *
     * @return array
     */
    public function getAll();

    /**
     * Check whether product type is set of products
     *
     * @param string $typeId
     * @return bool
     */
    public function isProductSet($typeId);

    /**
     * Get composable types
     *
     * @return array
     */
    public function getComposableTypes();

    /**
     * Get list of product types that comply with condition
     *
     * @param string $customAttributeName
     * @param string $value
     * @return array
     */
    public function filter($customAttributeName, $value = 'true');
}
