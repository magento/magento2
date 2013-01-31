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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order create totals block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Sales_Order_Create_Totals extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    protected $_totalRenderers;
    protected $_defaultRenderer = 'Mage_Adminhtml_Block_Sales_Order_Create_Totals_Default';

    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_totals');
    }

    public function getTotals()
    {
        return $this->getQuote()->getTotals();
    }

    public function getHeaderText()
    {
        return Mage::helper('Mage_Sales_Helper_Data')->__('Order Totals');
    }

    public function getHeaderCssClass()
    {
        return 'head-money';
    }

    protected function _getTotalRenderer($code)
    {
        $blockName = $code.'_total_renderer';
        $block = $this->getLayout()->getBlock($blockName);
        if (!$block) {
            $block = $this->_defaultRenderer;
            $config = Mage::getConfig()->getNode("global/sales/quote/totals/{$code}/admin_renderer");
            if ($config) {
                $block = (string) $config;
            }

            $block = $this->getLayout()->createBlock($block, $blockName);
        }
        /**
         * Transfer totals to renderer
         */
        $block->setTotals($this->getTotals());
        return $block;
    }

    public function renderTotal($total, $area = null, $colspan = 1)
    {
        return $this->_getTotalRenderer($total->getCode())
            ->setTotal($total)
            ->setColspan($colspan)
            ->setRenderingArea($area === null ? -1 : $area)
            ->toHtml();
    }

    public function renderTotals($area = null, $colspan = 1)
    {
        $html = '';
        foreach($this->getTotals() as $total) {
            if ($total->getArea() != $area && $area != -1) {
                continue;
            }
            $html .= $this->renderTotal($total, $area, $colspan);
        }
        return $html;
    }

    public function canSendNewOrderConfirmationEmail()
    {
        return Mage::helper('Mage_Sales_Helper_Data')->canSendNewOrderConfirmationEmail($this->getQuote()->getStoreId());
    }
}
