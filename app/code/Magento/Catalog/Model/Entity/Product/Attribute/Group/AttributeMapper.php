<?php
/**
 * Attribute mapper
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Entity\Product\Attribute\Group;

use Magento\Catalog\Model\Attribute;

/**
 * Class \Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapper
 *
 * @since 2.0.0
 */
class AttributeMapper implements AttributeMapperInterface
{
    /**
     * Unassignable attributes
     *
     * @var array
     * @since 2.0.0
     */
    protected $unassignableAttributes;

    /**
     * @param \Magento\Catalog\Model\Attribute\Config $attributeConfig
     * @since 2.0.0
     */
    public function __construct(Attribute\Config $attributeConfig)
    {
        $this->unassignableAttributes = $attributeConfig->getAttributeNames('unassignable');
    }

    /**
     * Build attribute representation
     *
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return array
     * @since 2.0.0
     */
    public function map(\Magento\Eav\Model\Entity\Attribute $attribute)
    {
        $isUnassignable = !in_array($attribute->getAttributeCode(), $this->unassignableAttributes);

        return [
            'text' => $attribute->getAttributeCode(),
            'id' => $attribute->getAttributeId(),
            'cls' => $isUnassignable ? 'leaf' : 'system-leaf',
            'allowDrop' => false,
            'allowDrag' => true,
            'leaf' => true,
            'is_user_defined' => $attribute->getIsUserDefined(),
            'is_unassignable' => $isUnassignable,
            'entity_id' => $attribute->getEntityAttributeId()
        ];
    }
}
