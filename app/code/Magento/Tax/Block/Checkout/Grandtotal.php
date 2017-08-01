<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Block\Checkout;

/**
 * Subtotal Total Row Renderer
 * @since 2.0.0
 */
class Grandtotal extends \Magento\Checkout\Block\Total\DefaultTotal
{
    /**
     * Path to template file
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'checkout/grandtotal.phtml';

    /**
     * @var \Magento\Tax\Model\Config
     * @since 2.0.0
     */
    protected $_taxConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $layoutProcessors
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Tax\Model\Config $taxConfig,
        array $layoutProcessors = [],
        array $data = []
    ) {
        $this->_taxConfig = $taxConfig;
        parent::__construct($context, $customerSession, $checkoutSession, $salesConfig, $layoutProcessors, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Check if we have include tax amount between grandtotal incl/excl tax
     *
     * @return bool
     * @since 2.0.0
     */
    public function includeTax()
    {
        if ($this->getTotal()->getValue()) {
            return $this->_taxConfig->displayCartTaxWithGrandTotal($this->getStore());
        }
        return false;
    }

    /**
     * Get grandtotal exclude tax
     *
     * @return float
     * @since 2.0.0
     */
    public function getTotalExclTax()
    {
        $excl = $this->getTotal()->getValue() - $this->_totals['tax']->getValue();
        $excl = max($excl, 0);
        return $excl;
    }
}
