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
 * Customer Tax Class
 */
class Mage_Tax_Model_Class_Type_Customer
    extends Mage_Tax_Model_Class_TypeAbstract
    implements Mage_Tax_Model_Class_Type_Interface
{
    /**
     * @var Mage_Customer_Model_Group
     */
    protected $_modelCustomerGroup;

    /**
     * @var Mage_Tax_Helper_Data
     */
    protected $_helper;

    /**
     * Class Type
     *
     * @var string
     */
    protected $_classType = Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER;

    /**
     * @param Mage_Tax_Model_Calculation_Rule $calculationRule
     * @param Mage_Customer_Model_Group $modelCustomerGroup
     * @param Mage_Tax_Helper_Data $helper
     * @param array $data
     */
    public function __construct(
        Mage_Tax_Model_Calculation_Rule $calculationRule,
        Mage_Customer_Model_Group $modelCustomerGroup,
        Mage_Tax_Helper_Data $helper,
        array $data = array()
    ) {
        parent::__construct($calculationRule, $data);
        $this->_modelCustomerGroup = $modelCustomerGroup;
        $this->_helper = $helper;
    }

    /**
     * Get Customer Groups with this tax class
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function getAssignedToObjects()
    {
        return $this->_modelCustomerGroup
            ->getCollection()
            ->addFieldToFilter('tax_class_id', $this->getId());
    }

    /**
     * Get Name of Objects that use this Tax Class Type
     *
     * @return string
     */
    public function getObjectTypeName()
    {
        return $this->_helper->__('customer group');
    }
}
