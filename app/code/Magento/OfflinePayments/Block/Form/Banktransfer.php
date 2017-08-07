<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Block\Form;

/**
 * Block for Bank Transfer payment method form
 */
class Banktransfer extends \Magento\OfflinePayments\Block\Form\AbstractInstruction
{
    /**
     * Bank transfer template
     *
     * @var string
     */
    protected $_template = 'form/banktransfer.phtml';
}
