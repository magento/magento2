<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Block\Checkout\Cart\Sidebar;

use Magento\Checkout\Block\Cart\Sidebar\Totals as SidebarTotals;

/**
 * Block for displaying totals in sidebar
 */
class Totals extends SidebarTotals
{
    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->_taxData = $taxHelper;
        $this->_taxConfig = $taxConfig;
        parent::__construct($context, $customerSession, $checkoutSession, $data);
    }

    /**
     * Get subtotal, including tax.
     *
     * @return float
     */
    public function getSubtotalInclTax()
    {
        $subtotal = 0;
        $totals = $this->getTotals();
        if (isset($totals['subtotal'])) {
            $subtotal = $totals['subtotal']->getValueInclTax();
            if (!$subtotal) {
                $subtotal = $totals['subtotal']->getValue();
            }
        }

        return $subtotal;
    }

    /**
     * Get subtotal, excluding tax.
     *
     * @return float
     */
    public function getSubtotalExclTax()
    {
        $subtotal = 0;
        $totals = $this->getTotals();
        if (isset($totals['subtotal'])) {
            $subtotal = $totals['subtotal']->getValueExclTax();
            if (!$subtotal) {
                $subtotal = $totals['subtotal']->getValue();
            }
        }
        return $subtotal;
    }

    /**
     * Return whether subtotal should be displayed including tax
     *
     * @return bool
     */
    public function getDisplaySubtotalInclTax()
    {
        return $this->_taxConfig->displayCartSubtotalInclTax();
    }

    /**
     * Return whether subtotal should be displayed excluding tax
     *
     * @return bool
     */
    public function getDisplaySubtotalExclTax()
    {
        return $this->_taxConfig->displayCartSubtotalExclTax();
    }

    /**
     * Return whether subtotal should be displayed excluding and including tax
     *
     * @return bool
     */
    public function getDisplaySubtotalBoth()
    {
        return $this->_taxConfig->displayCartSubtotalBoth();
    }
}
