<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Api\Data;

interface ProductLinkTypeInterface
{
    /**
     * Get link type code
     *
     * @return int
     */
    public function getCode();

    /**
     * Get link type name
     *
     * @return string
     */
    public function getName();
}
