<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        array $data = array()
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

    /**
     * Get incl/excl tax label
     *
     * @param bool $flag
     * @return string
     */
    public function getIncExcTaxLabel($flag)
    {
        $text = $this->_taxData->getIncExcText($flag);
        return $text ? ' (' . $text . ')' : '';
    }
}
