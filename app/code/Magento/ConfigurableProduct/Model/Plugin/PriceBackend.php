<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Make price validation optional for configurable product
 */
class PriceBackend
{
    /**
     * Around validate
     *
     * @param \Magento\Catalog\Model\Product\Attribute\Backend\Price $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $object
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate(
        \Magento\Catalog\Model\Product\Attribute\Backend\Price $subject,
        \Closure $proceed,
        $object
    ) {
        if ($object instanceof ProductInterface && $object->getTypeId() === Configurable::TYPE_CODE) {
            return true;
        }

        return $proceed($object);
    }
}
