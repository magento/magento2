<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Block\Form;

/**
 * Block for Bank Transfer payment method form
 */
class Banktransfer extends \Magento\Payment\Block\Form
{
    /**
     * Instructions text
     *
     * @var string
     */
    protected $_instructions;

    /**
     * Bank transfer template
     *
     * @var string
     */
    protected $_template = 'form/banktransfer.phtml';

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        if (is_null($this->_instructions)) {
            $this->_instructions = $this->getMethod()->getInstructions();
        }
        return $this->_instructions;
    }
}
