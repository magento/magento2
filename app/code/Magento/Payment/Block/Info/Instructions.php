<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Info;

/**
 * Block for Bank Transfer payment generic info
 *
 * @api
 * @since 2.0.0
 */
class Instructions extends \Magento\Payment\Block\Info
{
    /**
     * Instructions text
     *
     * @var string
     * @since 2.0.0
     */
    protected $_instructions;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'info/instructions.phtml';

    /**
     * Get instructions text from order payment
     * (or from config, if instructions are missed in payment)
     *
     * @return string
     * @since 2.0.0
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
