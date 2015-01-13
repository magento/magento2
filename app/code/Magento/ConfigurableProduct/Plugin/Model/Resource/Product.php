<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Plugin\Model\Resource;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Product
{
    /**
     * We need reset attribute set id to attribute after related simple product was saved
     *
     * @param \Magento\Catalog\Model\Resource\Product $subject
     * @param \Magento\Framework\Object $object
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Catalog\Model\Resource\Product $subject,
        \Magento\Framework\Object $object
    ) {
        /** @var \Magento\Catalog\Model\Product $object */
        if ($object->getTypeId() == Configurable::TYPE_CODE) {
            $object->getTypeInstance()->getSetAttributes($object);
        }
    }
}
