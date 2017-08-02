<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model\Product\Attribute\Source;

/**
 * Catalog product Msrp "Display Actual Price" attribute source
 * @since 2.0.0
 */
class Type extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Display Product Price on gesture
     */
    const TYPE_ON_GESTURE = 1;

    /**
     * Display Product Price in cart
     */
    const TYPE_IN_CART = 2;

    /**
     * Display Product Price before order confirmation
     */
    const TYPE_BEFORE_ORDER_CONFIRM = 3;

    /**
     * Get all options
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('On Gesture'), 'value' => self::TYPE_ON_GESTURE],
                ['label' => __('In Cart'), 'value' => self::TYPE_IN_CART],
                ['label' => __('Before Order Confirmation'), 'value' => self::TYPE_BEFORE_ORDER_CONFIRM],
            ];
        }
        return $this->_options;
    }
}
