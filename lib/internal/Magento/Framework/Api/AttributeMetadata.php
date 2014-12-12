<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Api;

/**
 * Base data object for custom attribute metadata
 */
class AttributeMetadata extends AbstractSimpleObject implements MetadataObjectInterface
{
    const ATTRIBUTE_CODE = 'attribute_code';

    /**
     * Retrieve code of the attribute.
     *
     * @return string|null
     */
    public function getAttributeCode()
    {
        return $this->_get(self::ATTRIBUTE_CODE);
    }
}
