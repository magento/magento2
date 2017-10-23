<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Totals;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Subtotal Total Row Renderer
 *
 * @author Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Discount extends \Magento\Sales\Block\Adminhtml\Order\Create\Totals\DefaultTotals
{
    //protected $_template = 'tax/checkout/subtotal.phtml';

    //protected $_template = 'tax/checkout/subtotal.phtml';
    protected $_taxConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->_taxConfig = $taxConfig;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $salesData, $salesConfig, $data);
    }

    /**
     * Display both
     *
     * @return bool
     */
    public function displayBoth()
    {
        return $this->_taxConfig->displayCartSubtotalBoth();
    }
}
