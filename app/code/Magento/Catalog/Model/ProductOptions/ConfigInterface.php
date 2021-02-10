<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ProductOptions;

/**
 * Interface \Magento\Catalog\Model\ProductOptions\ConfigInterface
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Get configuration of product type by name
     *
     * @param string $name
     * @return array
     */
    public function getOption($name);

    /**
     * Get configuration of all registered product types
     *
     * @return array
     */
    public function getAll();
}
