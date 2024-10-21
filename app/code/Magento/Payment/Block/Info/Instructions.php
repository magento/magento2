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
 * @since 100.0.2
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
     * Get instructions text from order payment (or from config, if instructions are missed in payment).
     *
     * @return string
     */
    public function getInstructions()
    {
        if ($this->_instructions === null) {
            $additionalInstructions = $this->getInfo()->getAdditionalInformation('instructions');
            if ($additionalInstructions) {
                $this->_instructions = $additionalInstructions;
                return $this->_instructions;
            }

            $instructions = $this->getMethod()->getConfigData('instructions');
            $this->_instructions = $instructions !== null ? trim($instructions) : '';
        }
        return $this->_instructions;
    }
}
