<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model\System\Config\Source;

class BmlSize
{
    /**
     * Options getter for Home Page and position Header
     *
     * @return array
     */
    public function getBmlSizeHPH()
    {
        return [
            '190x100' => '190 x 100',
            '234x60' => '234 x 60',
            '300x50' => '300 x 50',
            '468x60' => '468 x 60',
            '728x90' => '728 x 90',
            '800x66' => '800 x 66'
        ];
    }

    /**
     * Options getter for Home Page and position Sidebar (right)
     *
     * @return array
     */
    public function getBmlSizeHPS()
    {
        return [
            '120x90' => '120 x 90',
            '190x100' => '190 x 100',
            '234x60' => '234 x 60',
            '120x240' => '120 x 240',
            '120x600' => '120 x 600',
            '234x400' => '234 x 400',
            '250x250' => '250 x 250'
        ];
    }

    /**
     * Options getter for Catalog Category Page and position Center
     *
     * @return array
     */
    public function getBmlSizeCCPC()
    {
        return [
            '190x100' => '190 x 100',
            '234x60' => '234 x 60',
            '300x50' => '300 x 50',
            '468x60' => '468 x 60',
            '728x90' => '728 x 90',
            '800x66' => '800 x 66'
        ];
    }

    /**
     * Options getter for Catalog Category Page and position Sidebar (right)
     *
     * @return array
     */
    public function getBmlSizeCCPS()
    {
        return [
            '120x90' => '120 x 90',
            '190x100' => '190 x 100',
            '234x60' => '234 x 60',
            '120x240' => '120 x 240',
            '120x600' => '120 x 600',
            '234x400' => '234 x 400',
            '250x250' => '250 x 250'
        ];
    }

    /**
     * Options getter for Catalog Product Page and position Center
     *
     * @return array
     */
    public function getBmlSizeCPPC()
    {
        return [
            '190x100' => '190 x 100',
            '234x60' => '234 x 60',
            '300x50' => '300 x 50',
            '468x60' => '468 x 60',
            '728x90' => '728 x 90',
            '800x66' => '800 x 66'
        ];
    }

    /**
     * Options getter for Catalog Product Page and position Near Bill Me Later checkout button
     *
     * @return array
     */
    public function getBmlSizeCPPN()
    {
        return [
            '120x90' => '120 x 90',
            '190x100' => '190 x 100',
            '234x60' => '234 x 60',
            '120x240' => '120 x 240',
            '120x600' => '120 x 600',
            '234x400' => '234 x 400',
            '250x250' => '250 x 250'
        ];
    }

    /**
     * Options getter for Checkout Cart Page and position Center
     *
     * @return array
     */
    public function getBmlSizeCheckoutC()
    {
        return [
            '234x60' => '234 x 60',
            '300x50' => '300 x 50',
            '468x60' => '468 x 60',
            '728x90' => '728 x 90',
            '800x66' => '800 x 66'
        ];
    }

    /**
     * Options getter for Checkout Cart Page and position Near Bill Me Later checkout button
     *
     * @return array
     */
    public function getBmlSizeCheckoutN()
    {
        return [
            '234x60' => '234 x 60',
            '300x50' => '300 x 50',
            '468x60' => '468 x 60'
        ];
    }
}
