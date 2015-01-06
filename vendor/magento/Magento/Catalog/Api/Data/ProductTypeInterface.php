<?php
/**
 * Product type details
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Api\Data;

interface ProductTypeInterface
{
    /**
     * Get product type code
     *
     * @return string
     */
    public function getName();

    /**
     * Get product type label
     *
     * @return string
     */
    public function getLabel();
}
