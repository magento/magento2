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
 * Adminhtml order tax totals block
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Totals_Tax extends Mage_Tax_Block_Sales_Order_Tax
{
    /**
     * @var Mage_Tax_Helper_Data
     */
    protected $_taxHelper;

    /**
     * @var Mage_Tax_Model_Calculation
     */
    protected $_taxCalculation;

    /**
     * @var Mage_Tax_Model_Sales_Order_Tax_Factory
     */
    protected $_taxOrderFactory;

    /**
     * Initialize dependencies
     *
     * @param Mage_Backend_Block_Template_Context $context
     * @param Mage_Tax_Model_Config $taxConfig
     * @param Mage_Tax_Helper_Data $taxHelper
     * @param Mage_Tax_Model_Calculation $taxCalculation
     * @param Mage_Tax_Model_Sales_Order_Tax_Factory $taxOrderFactory
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_Tax_Model_Config $taxConfig,
        Mage_Tax_Helper_Data $taxHelper,
        Mage_Tax_Model_Calculation $taxCalculation,
        Mage_Tax_Model_Sales_Order_Tax_Factory $taxOrderFactory,
        array $data = array()
    ) {
        $this->_taxHelper = $taxHelper;
        $this->_taxCalculation = $taxCalculation;
        $this->_taxOrderFactory = $taxOrderFactory;
        parent::__construct($context, $taxConfig, $data);
    }

    /**
     * Get full information about taxes applied to order
     *
     * @return array
     */
    public function getFullTaxInfo()
    {
        /** @var $source Mage_Sales_Model_Order */
        $source = $this->getOrder();
        $taxClassAmount = array();
        if ($source instanceof Mage_Sales_Model_Order) {
            $taxClassAmount = $this->_taxHelper->getCalculatedTaxes($source);
            $shippingTax    = $this->_taxHelper->getShippingTax($source);
            $taxClassAmount = array_merge($taxClassAmount, $shippingTax);
            if (empty($taxClassAmount)) {
                $rates = $this->_taxOrderFactory->create()->getCollection()->loadByOrder($source)->toArray();
                $taxClassAmount =  $this->_taxCalculation->reproduceProcess($rates['items']);
            }
        }
        return $taxClassAmount;
    }

    /**
     * Display tax amount
     *
     * @param string $amount
     * @param string $baseAmount
     * @return string
     */
    public function displayAmount($amount, $baseAmount)
    {
        return $this->_helperFactory->get('Mage_Adminhtml_Helper_Sales')->displayPrices(
            $this->getSource(), $baseAmount, $amount, false, '<br />'
        );
    }

    /**
     * Get store object for process configuration settings
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return Mage::app()->getStore();
    }
}
