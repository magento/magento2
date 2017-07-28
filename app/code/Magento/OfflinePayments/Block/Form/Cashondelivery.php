<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Block\Form;

/**
 * Block for Cash On Delivery payment method form
 * @since 2.0.0
 */
class Cashondelivery extends \Magento\OfflinePayments\Block\Form\AbstractInstruction
{
    /**
     * Cash on delivery template
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'form/cashondelivery.phtml';
}
