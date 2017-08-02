<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Block\Form;

/**
 * Block for Bank Transfer payment method form
 * @since 2.0.0
 */
class Banktransfer extends \Magento\OfflinePayments\Block\Form\AbstractInstruction
{
    /**
     * Bank transfer template
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'form/banktransfer.phtml';
}
