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
 * Adminhtml common tax class controller
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Tax_ClassController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Save Tax Class via AJAX
     */
    public function ajaxSaveAction()
    {
        $responseContent = '';
        try {
            $classData = array(
                'class_id' => (int)$this->getRequest()->getPost('class_id') ?: null, // keep null for new tax classes
                'class_type' => $this->_processClassType((string)$this->getRequest()->getPost('class_type')),
                'class_name' => $this->_processClassName((string)$this->getRequest()->getPost('class_name'))
            );
            $class = Mage::getModel('Mage_Tax_Model_Class')
                ->setData($classData)
                ->save();
            $responseContent = Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                'success' => true,
                'error' => false,
                'error_message' => '',
                'class_id' => $class->getId(),
                'class_name' => $class->getClassName()
            ));
        } catch (Mage_Core_Exception $e) {
            $responseContent = Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                'success' => false,
                'error' => true,
                'error_message' => $e->getMessage(),
                'class_id' => '',
                'class_name' => ''
            ));
        } catch (Exception $e) {
            $responseContent = Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                'success' => false,
                'error' => true,
                'error_message' => Mage::helper('Mage_Tax_Helper_Data') ->__('There was an error saving tax class.'),
                'class_id' => '',
                'class_name' => ''
            ));
        }
        $this->getResponse()->setBody($responseContent);
    }

    /**
     * Delete Tax Class via AJAX
     */
    public function ajaxDeleteAction()
    {
        $classId = (int)$this->getRequest()->getParam('class_id');
        try {
            $classModel = Mage::getModel('Mage_Tax_Model_Class')->load($classId);
            Mage::register('tax_class_model', $classModel);
            $this->_checkTaxClassUsage($classModel);
            $classModel->delete();
            $responseContent = Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                'success' => true,
                'error' => false,
                'error_message' => ''
            ));
        } catch (Mage_Core_Exception $e) {
            $responseContent = Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                'success' => false,
                'error' => true,
                'error_message' => $e->getMessage()
            ));
        } catch (Exception $e) {
            $responseContent = Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                'success' => false,
                'error' => true,
                'error_message' => Mage::helper('Mage_Tax_Helper_Data')->__('An error occurred while deleting this tax class.')
            ));
        }
        $this->getResponse()->setBody($responseContent);
    }

    /**
     * Validate/Filter Tax Class Type
     *
     * @param string $classType
     * @return string processed class type
     * @throws Mage_Core_Exception
     */
    protected function _processClassType($classType)
    {
        $validClassTypes = array(
            Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER,
            Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT
        );
        if (!in_array($classType, $validClassTypes)) {
            Mage::throwException(Mage::helper('Mage_Tax_Helper_Data') ->__('Invalid type of tax class specified.'));
        }
        return $classType;
    }

    /**
     * Validate/Filter Tax Class Name
     *
     * @param string $className
     * @return string processed class name
     * @throws Mage_Core_Exception
     */
    protected function _processClassName($className)
    {
        $className = trim(strip_tags($className));
        if ($className == '') {
            Mage::throwException(Mage::helper('Mage_Tax_Helper_Data') ->__('Invalid name of tax class specified.'));
        }
        return $className;
    }

    /**
     * Check if customer tax class exists and has not been used yet (in Tax Rules or Customer Groups)
     *
     * @param Mage_Tax_Model_Class $classModel
     * @throws Mage_Core_Exception
     */
    protected function _checkTaxClassUsage(Mage_Tax_Model_Class $classModel)
    {
        if (!$classModel->getId()) {
            Mage::throwException(Mage::helper('Mage_Tax_Helper_Data')->__('This class no longer exists.'));
        }

        $ruleCollection = Mage::getModel('Mage_Tax_Model_Calculation_Rule')->getCollection()
            ->setClassTypeFilter($classModel->getClassType(), $classModel->getId());

        if ($ruleCollection->getSize() > 0) {
            Mage::throwException(Mage::helper('Mage_Tax_Helper_Data')->__('You cannot delete this tax class as it is used in Tax Rules. You have to delete the rules it is used in first.'));
        }

        $customerGroupCollection = Mage::getModel('Mage_Customer_Model_Group')->getCollection()
            ->addFieldToFilter('tax_class_id', $classModel->getId());
        $groupCount = $customerGroupCollection->getSize();
        if ($groupCount > 0) {
            Mage::throwException(Mage::helper('Mage_Tax_Helper_Data')->__('You cannot delete this tax class as it is used for %d customer groups.', $groupCount));
        }
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Tax::manage_tax');
    }
}
