<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Block\Checkout;

/**
 * Multishipping checkout state
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 */
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
     * Return multishipping steps
     *
     * @return array
     */
    public function getSteps()
    {
        return $this->_multishippingState->getSteps();
    }

    /**
     * Return multishipping steps to render
     *
     * @return array
     */
    public function getStepsToRender(): array
    {
        $allSteps = $this->_multishippingState->getSteps();
        array_splice($allSteps, -2);

        return $allSteps;
    }
}
