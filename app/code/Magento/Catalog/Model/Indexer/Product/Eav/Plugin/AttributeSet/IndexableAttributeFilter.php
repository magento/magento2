<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet;


class IndexableAttributeFilter
{
    /**
     * @var \Magento\Catalog\Model\Resource\Eav\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @param \Magento\Catalog\Model\Resource\Eav\AttributeFactory $attributeFactory
     */
    public function __construct(\Magento\Catalog\Model\Resource\Eav\AttributeFactory $attributeFactory)
    {
        $this->_attributeFactory = $attributeFactory;
    }

    /**
     * Retrieve codes of indexable attributes from given attribute set
     *
     * @param \Magento\Eav\Model\Entity\Attribute\Set $set
     * @return array
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
