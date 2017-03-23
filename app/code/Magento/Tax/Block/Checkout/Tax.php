<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax Total Row Renderer
 */
namespace Magento\Tax\Block\Checkout;

class Tax extends \Magento\Checkout\Block\Total\DefaultTotal
{
    /**
     * @var string
     */
    protected $_template = 'checkout/tax.phtml';
}
