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
 * @category    Magento
 * @package     Magento_Tax
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax totals modification block. Can be used just as subblock of \Magento\Sales\Block\Order\Totals
 */
namespace Magento\Tax\Block\Sales\Order;

class Tax extends \Magento\Core\Block\Template
{
    /**
     * Tax configuration model
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;
    protected $_order;
    protected $_source;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = array()
    ) {
        $this->_config = $taxConfig;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return $this->_config->displaySalesFullSummary($this->getOrder()->getStore());
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Object
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    public function initTotals()
    {
        /** @var $parent \Magento\Adminhtml\Block\Sales\Order\Invoice\Totals */
        $parent = $this->getParentBlock();
        $this->_order   = $parent->getOrder();
        $this->_source  = $parent->getSource();

        $store = $this->getStore();
        $allowTax = ($this->_source->getTaxAmount() > 0) || ($this->_config->displaySalesZeroTax($store));
        $grandTotal = (float) $this->_source->getGrandTotal();
        if (!$grandTotal || ($allowTax && !$this->_config->displaySalesTaxWithGrandTotal($store))) {
            $this->_addTax();
        }

        $this->_initSubtotal();
        $this->_initShipping();
        $this->_initDiscount();
        $this->_initGrandTotal();
        return $this;
    }

    /**
     * Add tax total string
     *
     * @param string $after
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    protected function _addTax($after='discount')
    {
        $taxTotal = new \Magento\Object(array(
            'code'      => 'tax',
            'block_name'=> $this->getNameInLayout()
        ));
        $this->getParentBlock()->addTotal($taxTotal, $after);
        return $this;
    }

    /**
     * Get order store object
     *
     * @return \Magento\Core\Model\Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }

    protected function _initSubtotal()
    {
        $store  = $this->getStore();
        $parent = $this->getParentBlock();
        $subtotal = $parent->getTotal('subtotal');
        if (!$subtotal) {
            return $this;
        }
        if ($this->_config->displaySalesSubtotalBoth($store)) {
            $subtotal       = (float) $this->_source->getSubtotal();
            $baseSubtotal   = (float) $this->_source->getBaseSubtotal();
            $subtotalIncl   = (float) $this->_source->getSubtotalInclTax();
            $baseSubtotalIncl= (float) $this->_source->getBaseSubtotalInclTax();

            if (!$subtotalIncl) {
                $subtotalIncl = $subtotal+ $this->_source->getTaxAmount()
                    - $this->_source->getShippingTaxAmount();
            }
            if (!$baseSubtotalIncl) {
                $baseSubtotalIncl = $baseSubtotal + $this->_source->getBaseTaxAmount()
                    - $this->_source->getBaseShippingTaxAmount();
            }
            $subtotalIncl = max(0, $subtotalIncl);
            $baseSubtotalIncl = max(0, $baseSubtotalIncl);
            $totalExcl = new \Magento\Object(array(
                'code'      => 'subtotal_excl',
                'value'     => $subtotal,
                'base_value'=> $baseSubtotal,
                'label'     => __('Subtotal (Excl.Tax)')
            ));
            $totalIncl = new \Magento\Object(array(
                'code'      => 'subtotal_incl',
                'value'     => $subtotalIncl,
                'base_value'=> $baseSubtotalIncl,
                'label'     => __('Subtotal (Incl.Tax)')
            ));
            $parent->addTotal($totalExcl, 'subtotal');
            $parent->addTotal($totalIncl, 'subtotal_excl');
            $parent->removeTotal('subtotal');
        } elseif ($this->_config->displaySalesSubtotalInclTax($store)) {
            $subtotalIncl   = (float) $this->_source->getSubtotalInclTax();
            $baseSubtotalIncl= (float) $this->_source->getBaseSubtotalInclTax();

            if (!$subtotalIncl) {
                $subtotalIncl = $this->_source->getSubtotal()
                    + $this->_source->getTaxAmount()
                    - $this->_source->getShippingTaxAmount();
            }
            if (!$baseSubtotalIncl) {
                $baseSubtotalIncl = $this->_source->getBaseSubtotal()
                    + $this->_source->getBaseTaxAmount()
                    - $this->_source->getBaseShippingTaxAmount();
            }

            $total = $parent->getTotal('subtotal');
            if ($total) {
                $total->setValue(max(0, $subtotalIncl));
                $total->setBaseValue(max(0, $baseSubtotalIncl));
            }
        }
        return $this;
    }

    protected function _initShipping()
    {
        $store  = $this->getStore();
        $parent = $this->getParentBlock();
        $shipping = $parent->getTotal('shipping');
        if (!$shipping) {
            return $this;
        }

        if ($this->_config->displaySalesShippingBoth($store)) {
            $shipping           = (float) $this->_source->getShippingAmount();
            $baseShipping       = (float) $this->_source->getBaseShippingAmount();
            $shippingIncl       = (float) $this->_source->getShippingInclTax();
            if (!$shippingIncl) {
                $shippingIncl   = $shipping + (float) $this->_source->getShippingTaxAmount();
            }
            $baseShippingIncl   = (float) $this->_source->getBaseShippingInclTax();
            if (!$baseShippingIncl) {
                $baseShippingIncl = $baseShipping + (float) $this->_source->getBaseShippingTaxAmount();
            }

            $totalExcl = new \Magento\Object(array(
                'code'      => 'shipping',
                'value'     => $shipping,
                'base_value'=> $baseShipping,
                'label'     => __('Shipping & Handling (Excl.Tax)')
            ));
            $totalIncl = new \Magento\Object(array(
                'code'      => 'shipping_incl',
                'value'     => $shippingIncl,
                'base_value'=> $baseShippingIncl,
                'label'     => __('Shipping & Handling (Incl.Tax)')
            ));
            $parent->addTotal($totalExcl, 'shipping');
            $parent->addTotal($totalIncl, 'shipping');
        } elseif ($this->_config->displaySalesShippingInclTax($store)) {
            $shippingIncl       = $this->_source->getShippingInclTax();
            if (!$shippingIncl) {
                $shippingIncl = $this->_source->getShippingAmount()
                    + $this->_source->getShippingTaxAmount();
            }
            $baseShippingIncl   = $this->_source->getBaseShippingInclTax();
            if (!$baseShippingIncl) {
                $baseShippingIncl = $this->_source->getBaseShippingAmount()
                    + $this->_source->getBaseShippingTaxAmount();
            }
            $total = $parent->getTotal('shipping');
            if ($total) {
                $total->setValue($shippingIncl);
                $total->setBaseValue($baseShippingIncl);
            }
        }
        return $this;
    }

    protected function _initDiscount()
    {
//        $store  = $this->getStore();
//        $parent = $this->getParentBlock();
//        if ($this->_config->displaySales) {
//
//        } elseif ($this->_config->displaySales) {
//
//        }
    }

    protected function _initGrandTotal()
    {
        $store  = $this->getStore();
        $parent = $this->getParentBlock();
        $grandototal = $parent->getTotal('grand_total');
        if (!$grandototal || !(float)$this->_source->getGrandTotal()) {
            return $this;
        }

        if ($this->_config->displaySalesTaxWithGrandTotal($store)) {
            $grandtotal         = $this->_source->getGrandTotal();
            $baseGrandtotal     = $this->_source->getBaseGrandTotal();
            $grandtotalExcl     = $grandtotal - $this->_source->getTaxAmount();
            $baseGrandtotalExcl = $baseGrandtotal - $this->_source->getBaseTaxAmount();
            $grandtotalExcl     = max($grandtotalExcl, 0);
            $baseGrandtotalExcl = max($baseGrandtotalExcl, 0);
            $totalExcl = new \Magento\Object(array(
                'code'      => 'grand_total',
                'strong'    => true,
                'value'     => $grandtotalExcl,
                'base_value'=> $baseGrandtotalExcl,
                'label'     => __('Grand Total (Excl.Tax)')
            ));
            $totalIncl = new \Magento\Object(array(
                'code'      => 'grand_total_incl',
                'strong'    => true,
                'value'     => $grandtotal,
                'base_value'=> $baseGrandtotal,
                'label'     => __('Grand Total (Incl.Tax)')
            ));
            $parent->addTotal($totalExcl, 'grand_total');
            $this->_addTax('grand_total');
            $parent->addTotal($totalIncl, 'tax');
        }
        return $this;
    }

    public function getOrder()
    {
        return $this->_order;
    }

    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
