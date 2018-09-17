<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Info;

/**
 * Block for Bank Transfer payment generic info
 */
class Instructions extends \Magento\Payment\Block\Info
{
    /**
     * Instructions text
     *
     * @var string
     */
    protected $_instructions;

    /**
     * @var string
     */
    protected $_template = 'Magento_Payment::info/instructions.phtml';

    /**
     * Get instructions text from order payment
     * (or from config, if instructions are missed in payment)
     *
     * @return string
     */
    public function getInstructions()
    {
        if ($this->_instructions === null) {
            $this->_instructions = $this->getInfo()->getAdditionalInformation(
                'instructions'
            ) ?: trim($this->getMethod()->getConfigData('instructions'));
        }
        return $this->_instructions;
    }
}
