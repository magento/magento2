<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
