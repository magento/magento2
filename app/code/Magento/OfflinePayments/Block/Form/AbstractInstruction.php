<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Block\Form;

/**
 * Abstract class for Cash On Delivery and Bank Transfer payment method form
 * @since 2.0.0
 */
abstract class AbstractInstruction extends \Magento\Payment\Block\Form
{
    /**
     * Instructions text
     *
     * @var string
     * @since 2.0.0
     */
    protected $_instructions;

    /**
     * Get instructions text from config
     *
     * @return null|string
     * @since 2.0.0
     */
    public function getInstructions()
    {
        if ($this->_instructions === null) {
            /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
            $method = $this->getMethod();
            $this->_instructions = $method->getConfigData('instructions');
        }
        return $this->_instructions;
    }
}
