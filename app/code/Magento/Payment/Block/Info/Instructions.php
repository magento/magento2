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
     * Gets payment method title for appropriate store.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title')
            ?: $this->getMethod()->getConfigData('title', $this->getInfo()->getOrder()->getStoreId());
    }

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
