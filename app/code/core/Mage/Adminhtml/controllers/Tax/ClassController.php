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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
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
     * save class action
     *
     */
    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {

            $model = Mage::getModel('Mage_Tax_Model_Class')->setData($postData);

            try {
                $model->save();
                $classId    = $model->getId();
                $classType  = $model->getClassType();
                $classUrl   = '*/tax_class_' . strtolower($classType);

                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Tax_Helper_Data')->__('The tax class has been saved.')
                );
                $this->_redirect($classUrl);

                return ;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setClassData($postData);
                $this->_redirectReferer();
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                    Mage::helper('Mage_Tax_Helper_Data')->__('An error occurred while saving this tax class.')
                );
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setClassData($postData);
                $this->_redirectReferer();
            }

            $this->_redirectReferer();
            return;
        }
        $this->getResponse()->setRedirect($this->getUrl('*/tax_class'));
    }

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
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $classType = strtolower($this->getRequest()->getParam('classType'));
        $this->loadLayout()
            ->_setActiveMenu('sales/tax/tax_classes_' . $classType)
            ->_addBreadcrumb(Mage::helper('Mage_Tax_Helper_Data')->__('Sales'), Mage::helper('Mage_Tax_Helper_Data')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('Mage_Tax_Helper_Data')->__('Tax'), Mage::helper('Mage_Tax_Helper_Data')->__('Tax'))
        ;

        return $this;
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Tax::classes_product')
            || Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Tax::classes_customer');
    }
}
