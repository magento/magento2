<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModuleMSC\Model\Data\Eav;

use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\Api\MetadataObjectInterface;

/**
 * Class AttributeMetadata
 */
class AttributeMetadata extends AbstractExtensibleObject implements MetadataObjectInterface
{
    /**#@+
     * Constants used as keys into $_data
     */
    const ATTRIBUTE_ID = 'attribute_id';

    const ATTRIBUTE_CODE = 'attribute_code';
    /**#@-*/

    /**
     * Retrieve id of the attribute.
     *
     * @return string|null
     */
    public function getAttributeId()
    {
        return $this->_get(self::ATTRIBUTE_ID);
    }

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
