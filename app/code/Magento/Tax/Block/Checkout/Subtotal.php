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
class Subtotal extends DefaultTotal
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'Magento_Tax::checkout/subtotal.phtml';

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
     * Get display including and excluding tax config
     *
     * @return bool
     */
    public function displayBoth()
    {
        return $this->_taxConfig->displayCartSubtotalBoth($this->getStore());
    }
}
