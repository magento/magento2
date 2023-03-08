<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Block\Checkout;

use Magento\Checkout\Block\Total\DefaultTotal;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ConfigInterface;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Subtotal Total Row Renderer
 */
class Grandtotal extends DefaultTotal
{
    /**
     * Path to template file
     *
     * @var string
     */
    protected $_template = 'Magento_Tax::checkout/grandtotal.phtml';

    /**
     * @var TaxConfig
     */
    protected $_taxConfig;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param ConfigInterface $salesConfig
     * @param TaxConfig $taxConfig
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        ConfigInterface $salesConfig,
        TaxConfig $taxConfig,
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
     */
    public function getTotalExclTax()
    {
        $excl = $this->getTotal()->getValue() - $this->_totals['tax']->getValue();
        $excl = max($excl, 0);
        return $excl;
    }
}
