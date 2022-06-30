<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Sales\Order\Shipment;

use Magento\Catalog\Model\Product\Type;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Validate if requested order items can be shipped according to bundle product shipment type
 */
class BundleShipmentTypeValidator implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function validate($item)
    {
        $result = [];
        if (!$item->isDummy(true)) {
            return $result;
        }

        $message = 'Cannot create shipment as bundle product "%1" has shipment type "%2". ' .
            '%3 should be shipped instead.';

        if ($item->getHasChildren() && $item->getProductType() === Type::TYPE_BUNDLE) {
            $result[] = __(
                $message,
                $item->getSku(),
                __('Separately'),
                __('Bundle product options'),
            );
        }

        if ($item->getParentItem() && $item->getParentItem()->getProductType() === Type::TYPE_BUNDLE) {
            $result[] = __(
                $message,
                $item->getParentItem()->getSku(),
                __('Together'),
                __('Bundle product itself'),
            );
        }

        return $result;
    }
}
