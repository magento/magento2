<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

/**
 * Source model for PayLater banner position
 */
class Position
{
    /**
     * PayLater positions source getter for Catalog Product Page
     *
     * @return array
     */
    public function getPositionsCPP(): array
    {
        return [
            'header' => __('Header (center)'),
            'near_pp_button' => __('Near PayPal Credit checkout button')
        ];
    }

    /**
     * PayLater positions source getter for Home Page
     *
     * @return array
     */
    public function getPositionsHP(): array
    {
        return [
            'header' => __('Header (center)'),
            'sidebar' => __('Sidebar')
        ];
    }

    /**
     * PayLater positions source getter for Checkout Page
     *
     * @return array
     */
    public function getPositionsCheckout(): array
    {
        return [
            'near_pp_button' => __('Near PayPal Credit checkout button')
        ];
    }

    /**
     * PayLater positions source getter for Catalog Category Page
     *
     * @return array
     */
    public function getPositionsCategoryPage(): array
    {
        return [
            'header' => __('Header (center)'),
            'sidebar' => __('Sidebar'),
        ];
    }

    /**
     * PayLater positions source getter for Checkout Cart Page
     *
     * @return array
     */
    public function getPositionsCart(): array
    {
        return [
            'header' => __('Header (center)'),
            'near_pp_button' => __('Near PayPal Credit checkout button')
        ];
    }
}
