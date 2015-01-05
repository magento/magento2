<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\ProductOptions;

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
