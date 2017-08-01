<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax Total Row Renderer
 */
namespace Magento\Tax\Block\Checkout;

/**
 * Class \Magento\Tax\Block\Checkout\Tax
 *
 * @since 2.0.0
 */
class Tax extends \Magento\Checkout\Block\Total\DefaultTotal
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'checkout/tax.phtml';
}
