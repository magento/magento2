<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model\System\Config\Source;

/**
 * Class \Magento\Paypal\Model\System\Config\Source\BmlPosition
 *
 * @since 2.0.0
 */
class BmlPosition
{
    /**
     * Bml positions source getter for Home Page
     *
     * @return array
     * @since 2.0.0
     */
    public function getBmlPositionsHP()
    {
        return [
            '0' => __('Header (center)'),
            '1' => __('Sidebar (right)')
        ];
    }

    /**
     * Bml positions source getter for Catalog Category Page
     *
     * @return array
     * @since 2.0.0
     */
    public function getBmlPositionsCCP()
    {
        return [
            '0' => __('Header (center)'),
            '1' => __('Sidebar (right)')
        ];
    }

    /**
     * Bml positions source getter for Catalog Product Page
     *
     * @return array
     * @since 2.0.0
     */
    public function getBmlPositionsCPP()
    {
        return [
            '0' => __('Header (center)'),
            '1' => __('Near PayPal Credit checkout button')
        ];
    }

    /**
     * Bml positions source getter for Checkout Cart Page
     *
     * @return array
     * @since 2.0.0
     */
    public function getBmlPositionsCheckout()
    {
        return [
            '0' => __('Header (center)'),
            '1' => __('Near PayPal Credit checkout button')
        ];
    }
}
