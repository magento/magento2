<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

interface MetadataObjectInterface
{
    /**
     * Retrieve code of the attribute.
     *
     * @return string
     */
    public function getAttributeCode();
}
