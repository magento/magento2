<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

interface ProductLinkAttributeInterface
{
    /**
     * Get attribute code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get attribute type
     *
     * @return string
     */
    public function getType();
}
