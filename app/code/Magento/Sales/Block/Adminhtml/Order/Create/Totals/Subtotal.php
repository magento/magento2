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
class Subtotal extends \Magento\Sales\Block\Adminhtml\Order\Create\Totals\DefaultTotals
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'Magento_Sales::order/create/totals/subtotal.phtml';

    /**
     * Tax config
     *
     * @var \Magento\Tax\Model\Config
     */
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
     * Check if we need display both sobtotals
     *
     * @return bool
     */
    public function displayBoth()
    {
        /**
         * Check without store parameter - we wil get admin configuration value
         */
        return $this->_taxConfig->displayCartSubtotalBoth();
    }
}
