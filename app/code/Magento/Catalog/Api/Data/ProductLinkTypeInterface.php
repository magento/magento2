<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
