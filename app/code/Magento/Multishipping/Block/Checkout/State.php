<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Multishipping checkout state
 */
namespace Magento\Multishipping\Block\Checkout;

class State extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping\State
     */
    protected $_multishippingState;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping\State $multishippingState
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping\State $multishippingState,
        array $data = []
    ) {
        $this->_multishippingState = $multishippingState;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve available checkout steps
     *
     * @return array
     */
    public function getSteps()
    {
        return $this->_multishippingState->getSteps();
    }
}
