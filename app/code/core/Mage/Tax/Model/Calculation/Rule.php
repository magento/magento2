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
 * @package     Mage_Tax
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax Rule Model
 *
 * @method Mage_Tax_Model_Resource_Calculation_Rule _getResource()
 * @method Mage_Tax_Model_Resource_Calculation_Rule getResource()
 * @method string getCode()
 * @method Mage_Tax_Model_Calculation_Rule setCode(string $value)
 * @method int getPriority()
 * @method Mage_Tax_Model_Calculation_Rule setPriority(int $value)
 * @method int getPosition()
 * @method Mage_Tax_Model_Calculation_Rule setPosition(int $value)
 *
 * @category    Mage
 * @package     Mage_Tax
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tax_Model_Calculation_Rule extends Mage_Core_Model_Abstract
{
    protected $_ctcs                = null;
    protected $_ptcs                = null;
    protected $_rates               = null;

    protected $_ctcModel            = null;
    protected $_ptcModel            = null;
    protected $_rateModel           = null;

    protected $_calculationModel    = null;

    /**
     * Helper
     *
     * @var Mage_Tax_Helper_Data
     */
    protected $_helper;

    /**
     * Tax Model Class
     *
     * @var Mage_Tax_Model_Class
     */
    protected $_taxClass;

    /**
     * Varien model constructor
     */
    public function __construct(
        Mage_Core_Model_Event_Manager $eventDispatcher,
        Mage_Core_Model_Cache $cacheManager,
        Mage_Tax_Helper_Data $taxHelper,
        Mage_Tax_Model_Class $taxClass,
        Mage_Core_Model_Resource_Abstract $resource = null,
        Varien_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct(
            $eventDispatcher,
            $cacheManager,
            $resource,
            $resourceCollection,
            $data
        );

        $this->_init('Mage_Tax_Model_Resource_Calculation_Rule');

        $this->_helper = $taxHelper;
        $this->_taxClass = $taxClass;
    }

    /**
     * After save rule
     * Redeclared for populate rate calculations
     *
     * @return Mage_Tax_Model_Calculation_Rule
     */
    protected function _afterSave()
    {
        parent::_afterSave();
        $this->saveCalculationData();
        Mage::dispatchEvent('tax_settings_change_after');
        return $this;
    }

    /**
     * After rule delete
     * redeclared for dispatch tax_settings_change_after event
     *
     * @return Mage_Tax_Model_Calculation_Rule
     */
    protected function _afterDelete()
    {
        Mage::dispatchEvent('tax_settings_change_after');
        return parent::_afterDelete();
    }

    public function saveCalculationData()
    {
        $ctc = $this->getData('tax_customer_class');
        $ptc = $this->getData('tax_product_class');
        $rates = $this->getData('tax_rate');

        Mage::getSingleton('Mage_Tax_Model_Calculation')->deleteByRuleId($this->getId());
        foreach ($ctc as $c) {
            foreach ($ptc as $p) {
                foreach ($rates as $r) {
                    $dataArray = array(
                        'tax_calculation_rule_id'   =>$this->getId(),
                        'tax_calculation_rate_id'   =>$r,
                        'customer_tax_class_id'     =>$c,
                        'product_tax_class_id'      =>$p,
                    );
                    Mage::getSingleton('Mage_Tax_Model_Calculation')->setData($dataArray)->save();
                }
            }
        }
    }

    public function getCalculationModel()
    {
        if ($this->_calculationModel === null) {
            $this->_calculationModel = Mage::getSingleton('Mage_Tax_Model_Calculation');
        }
        return $this->_calculationModel;
    }

    public function getRates()
    {
        return $this->getCalculationModel()->getRates($this->getId());
    }

    public function getCustomerTaxClasses()
    {
        return $this->getCalculationModel()->getCustomerTaxClasses($this->getId());
    }

    public function getProductTaxClasses()
    {
        return $this->getCalculationModel()->getProductTaxClasses($this->getId());
    }

    /**
     * Check Customer Tax Class and if it is empty - use defaults
     *
     * @return int|array|null
     */
    public function getCustomerTaxClassWithDefault()
    {
        $customerClasses = $this->getAllOptionsForClass(Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER);
        if (empty($customerClasses)) {
            return null;
        }

        $configValue = $this->_helper->getDefaultCustomerTaxClass();
        if (!empty($configValue)) {
            return $configValue;
        }

        $firstClass = array_shift($customerClasses);
        return isset($firstClass['value']) ? $firstClass['value'] : null;
    }

    /**
     * Check Product Tax Class and if it is empty - use defaults
     *
     * @return int|array|null
     */
    public function getProductTaxClassWithDefault()
    {
        $productClasses = $this->getAllOptionsForClass(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT);
        if (empty($productClasses)) {
            return null;
        }

        $configValue = $this->_helper->getDefaultProductTaxClass();
        if (!empty($configValue)) {
            return $configValue;
        }

        $firstClass = array_shift($productClasses);
        return isset($firstClass['value']) ? $firstClass['value'] : null;
    }

    /**
     * Get all possible options for specified class name (customer|product)
     *
     * @param string $classFilter
     * @return array
     */
    public function getAllOptionsForClass($classFilter) {
        $classes = $this->_taxClass
            ->getCollection()
            ->setClassTypeFilter($classFilter)
            ->toOptionArray();

        return $classes;
    }
}

