<?php
/**
 * Attribute mapper
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Entity\Product\Attribute\Group;

use Magento\Catalog\Model\Attribute;

class AttributeMapper implements AttributeMapperInterface
{
    /**
     * Unassignable attributes
     *
     * @var array
     */
    protected $unassignableAttributes;

    /**
     * @param \Magento\Catalog\Model\Attribute\Config $attributeConfig
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
