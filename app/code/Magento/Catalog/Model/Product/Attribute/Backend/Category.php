<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product categories backend attribute model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

/**
 * Class \Magento\Catalog\Model\Product\Attribute\Backend\Category
 *
 */
class Category extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Set category ids to product data
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $object->setData($this->getAttribute()->getAttributeCode(), $object->getCategoryIds());
        return parent::afterLoad($object);
    }
}
