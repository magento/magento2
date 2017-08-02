<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ProductTypes;

/**
 * Provides product types configuration
 *
 * @api
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Get configuration of product type by name
     *
     * @param string $name
     * @return array
     * @since 2.0.0
     */
    public function getType($name);

    /**
     * Get configuration of all registered product types
     *
     * @return array
     * @since 2.0.0
     */
    public function getAll();

    /**
     * Check whether product type is set of products
     *
     * @param string $typeId
     * @return bool
     * @since 2.0.0
     */
    public function isProductSet($typeId);

    /**
     * Get composable types
     *
     * @return array
     * @since 2.0.0
     */
    public function getComposableTypes();

    /**
     * Get list of product types that comply with condition
     *
     * @param string $customAttributeName
     * @param string $value
     * @return array
     * @since 2.0.0
     */
    public function filter($customAttributeName, $value = 'true');
}
