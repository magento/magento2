<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Multishipping checkout state
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Multishipping\Block\Checkout;

/**
 * Class \Magento\Multishipping\Block\Checkout\State
 *
 * @since 2.0.0
 */
class State extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping\State
     * @since 2.0.0
     */
    protected $_multishippingState;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping\State $multishippingState
     * @param array $data
     * @since 2.0.0
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
     * @return array
     * @since 2.0.0
     */
    public function getSteps()
    {
        return $this->_multishippingState->getSteps();
    }
}
