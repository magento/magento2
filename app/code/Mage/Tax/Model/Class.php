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
 * Tax class model
 *
 * @method Mage_Tax_Model_Resource_Class _getResource()
 * @method Mage_Tax_Model_Resource_Class getResource()
 * @method string getClassName()
 * @method Mage_Tax_Model_Class setClassName(string $value)
 * @method string getClassType()
 * @method Mage_Tax_Model_Class setClassType(string $value)
 *
 * @category    Mage
 * @package     Mage_Tax
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Tax_Model_Class extends Mage_Core_Model_Abstract
{
    /**
     * Defines Customer Tax Class string
     */
    const TAX_CLASS_TYPE_CUSTOMER = 'CUSTOMER';

    /**
     * Defines Product Tax Class string
     */
    const TAX_CLASS_TYPE_PRODUCT = 'PRODUCT';

    /**
     * @var Mage_Tax_Model_Class_Factory
     */
    protected $_classFactory;

    /**
     * @var Mage_Tax_Helper_Data
     */
    protected $_helper;

    /**
     * @param Mage_Core_Model_Context $context
     * @param Mage_Core_Model_Resource_Abstract $resource
     * @param Varien_Data_Collection_Db $resourceCollection
     * @param Mage_Tax_Model_Class_Factory $classFactory
     * @param Mage_Tax_Helper_Data $helper
     * @param array $data
     */
    public function __construct(
        Mage_Core_Model_Context $context,
        Mage_Tax_Model_Class_Factory $classFactory,
        Mage_Tax_Helper_Data $helper,
        Mage_Core_Model_Resource_Abstract $resource = null,
        Varien_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->_classFactory = $classFactory;
        $this->_helper = $helper;
    }

    public function _construct()
    {
        $this->_init('Mage_Tax_Model_Resource_Class');
    }

    /**
     * Check whether this class can be deleted
     *
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function checkClassCanBeDeleted()
    {
        if (!$this->getId()) {
            Mage::throwException($this->_helper->__('This class no longer exists.'));
        }

        $typeModel = $this->_classFactory->create($this);

        if ($typeModel->getAssignedToRules()->getSize() > 0) {
            Mage::throwException($this->_helper->__('You cannot delete this tax class because it is used in Tax Rules. You have to delete the rules it is used in first.'));
        }

        $objectCount = $typeModel->getAssignedToObjects()->getSize();
        if ($objectCount > 0) {
            Mage::throwException($this->_helper->__('You cannot delete this tax class because it is used for %d %s(s).', $objectCount, $typeModel->getObjectTypeName()));
        }

        return true;
    }
}
