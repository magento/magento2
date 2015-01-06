<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule1\Service\V1\Entity\Eav;

use Magento\Framework\Api\AttributeMetadataBuilderInterface;
use Magento\Framework\Api\ExtensibleObjectBuilder;

/**
 * Class AttributeMetadataBuilder
 */
class AttributeMetadataBuilder extends ExtensibleObjectBuilder implements AttributeMetadataBuilderInterface
{
    /**
     * Set attribute id
     *
     * @param  int $attributeId
     * @return $this
     */
    public function setAttributeId($attributeId)
    {
        return $this->_set(AttributeMetadata::ATTRIBUTE_ID, $attributeId);
    }

    /**
     * Set attribute code
     *
     * @param  string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->_set(AttributeMetadata::ATTRIBUTE_CODE, $attributeCode);
    }
}
