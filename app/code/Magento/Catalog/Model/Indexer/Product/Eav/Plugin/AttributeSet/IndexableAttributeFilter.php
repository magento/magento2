<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet;

/**
 * Class \Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet\IndexableAttributeFilter
 *
 * @since 2.0.0
 */
class IndexableAttributeFilter
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     * @since 2.0.0
     */
    protected $_attributeFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     * @since 2.0.0
     */
    public function __construct(\Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory)
    {
        $this->_attributeFactory = $attributeFactory;
    }

    /**
     * Retrieve codes of indexable attributes from given attribute set
     *
     * @param \Magento\Eav\Model\Entity\Attribute\Set $set
     * @return array
     * @since 2.0.0
     */
    public function filter(\Magento\Eav\Model\Entity\Attribute\Set $set)
    {
        $codes = [];
        $catalogResource = $this->_attributeFactory->create();
        $groups = $set->getGroups();
        if (is_array($groups)) {
            foreach ($groups as $group) {
                /** @var $group \Magento\Eav\Model\Entity\Attribute\Group */
                foreach ($group->getAttributes() as $attribute) {
                    /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
                    $catalogResource->load($attribute->getId());
                    if ($catalogResource->isIndexable()) {
                        // Attribute requires to be cloned for new dataset to maintain attribute set changes
                        $attributeClone = clone $attribute;
                        $attributeClone->load($attribute->getAttributeId());
                        $codes[] = $attributeClone->getAttributeCode();
                        unset($attributeClone);
                    }
                }
            }
        }
        return $codes;
    }
}
