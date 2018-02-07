<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Plugin\Model\ResourceModel;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Product
{
    /**
     * We need reset attribute set id to attribute after related simple product was saved
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product $subject
     * @param \Magento\Framework\DataObject $object
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Magento\Framework\DataObject $object
    ) {
        /** @var \Magento\Catalog\Model\Product $object */
        if ($object->getTypeId() == Configurable::TYPE_CODE) {
            $object->getTypeInstance()->getSetAttributes($object);
        }
    }
}
