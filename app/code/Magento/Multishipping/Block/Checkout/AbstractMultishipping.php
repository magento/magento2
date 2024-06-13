<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Multishipping checkout base abstract block
 */
namespace Magento\Multishipping\Block\Checkout;

class AbstractMultishipping extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected $_multishipping;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        array $data = []
    ) {
        $this->_multishipping = $multishipping;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve multishipping checkout model
     *
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    public function getCheckout()
    {
        return $this->_multishipping;
    }
}
