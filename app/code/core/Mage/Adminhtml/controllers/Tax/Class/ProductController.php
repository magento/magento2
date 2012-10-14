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
 * Adminhtml product tax class controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Tax_Class_ProductController extends Mage_Adminhtml_Controller_Action
{
    /**
     * view grid
     *
     */
    public function indexAction()
    {
        $this->_title($this->__('Sales'))
             ->_title($this->__('Tax'))
             ->_title($this->__('Product Tax Classes'));

        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock('Mage_Adminhtml_Block_Tax_Class')
                    ->setClassType(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT)
            )
            ->renderLayout();
    }

    /**
     * new class action
     *
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * edit class action
     *
     */
    public function editAction()
    {
        $this->_title($this->__('Sales'))
             ->_title($this->__('Tax'))
             ->_title($this->__('Product Tax Classes'));

        $classId    = $this->getRequest()->getParam('id');
        $model      = Mage::getModel('Mage_Tax_Model_Class');
        if ($classId) {
            $model->load($classId);
            if (!$model->getId() || $model->getClassType() != Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                    Mage::helper('Mage_Tax_Helper_Data')->__('This class no longer exists')
                );
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getClassName() : $this->__('New Class'));

        $data = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getClassData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('tax_class', $model);

        $this->_initAction()
            ->_addBreadcrumb(
                $classId ? Mage::helper('Mage_Tax_Helper_Data')->__('Edit Class') :  Mage::helper('Mage_Tax_Helper_Data')->__('New Class'),
                $classId ?  Mage::helper('Mage_Tax_Helper_Data')->__('Edit Class') :  Mage::helper('Mage_Tax_Helper_Data')->__('New Class')
            )
            ->_addContent(
                $this->getLayout()->createBlock('Mage_Adminhtml_Block_Tax_Class_Edit')
                    ->setData('action', $this->getUrl('*/tax_class/save'))
                    ->setClassType(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT)
            )
            ->renderLayout();
    }

    /**
     * delete class action
     *
     */
    public function deleteAction()
    {
        $classId    = $this->getRequest()->getParam('id');
        $session    = Mage::getSingleton('Mage_Adminhtml_Model_Session');
        $classModel = Mage::getModel('Mage_Tax_Model_Class')
            ->load($classId);

        if (!$classModel->getId() || $classModel->getClassType() != Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT) {
            $session->addError(Mage::helper('Mage_Tax_Helper_Data')->__('This class no longer exists'));
            $this->_redirect('*/*/');
            return;
        }

        $ruleCollection = Mage::getModel('Mage_Tax_Model_Calculation_Rule')
            ->getCollection()
            ->setClassTypeFilter(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT, $classId);

        if ($ruleCollection->getSize() > 0) {
            $session->addError(Mage::helper('Mage_Tax_Helper_Data')->__('You cannot delete this tax class as it is used in Tax Rules. You have to delete the rules it is used in first.'));
            $this->_redirect('*/*/edit/', array('id' => $classId));
            return;
        }

        $productCollection = Mage::getModel('Mage_Catalog_Model_Product')
            ->getCollection()
            ->addAttributeToFilter('tax_class_id', $classId);
        $productCount = $productCollection->getSize();

        if ($productCount > 0) {
            $session->addError(Mage::helper('Mage_Tax_Helper_Data')->__('You cannot delete this tax class as it is used for %d products.', $productCount));
            $this->_redirect('*/*/edit/', array('id' => $classId));
            return;
        }

        try {
            $classModel->delete();

            $session->addSuccess(Mage::helper('Mage_Tax_Helper_Data')->__('The tax class has been deleted.'));
            $this->getResponse()->setRedirect($this->getUrl("*/*/"));
            return;
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('Mage_Tax_Helper_Data')->__('An error occurred while deleting this tax class.'));
        }

        $this->_redirect('*/*/edit/', array('id' => $classId));
    }

    /**
     * Delete Tax Class via AJAX
     */
    public function ajaxDeleteAction()
    {
        $responseContent = '';
        $classId = (int)$this->getRequest()->getParam('class_id');
        try {
            $classModel = Mage::getModel('Mage_Tax_Model_Class')->load($classId);
            $this->_checkProductTaxClassUsage($classModel);
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
     * Check if product tax class exists and has not been used yet (in Tax Rules or Products)
     *
     * @param Mage_Tax_Model_Class $classModel
     */
    protected function _checkProductTaxClassUsage(Mage_Tax_Model_Class $classModel)
    {
        if (!$classModel->getId() || $classModel->getClassType() != Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT) {
            Mage::throwException(Mage::helper('Mage_Tax_Helper_Data')->__('This class no longer exists.'));
        }

        $ruleCollection = Mage::getModel('Mage_Tax_Model_Calculation_Rule')->getCollection()
            ->setClassTypeFilter(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT, $classModel->getId());

        if ($ruleCollection->getSize() > 0) {
            Mage::throwException(Mage::helper('Mage_Tax_Helper_Data')->__('You cannot delete this tax class as it is used in Tax Rules. You have to delete the rules it is used in first.'));
        }

        $productCollection = Mage::getModel('Mage_Catalog_Model_Product')->getCollection()
            ->addAttributeToFilter('tax_class_id', $classModel->getId());
        $productCount = $productCollection->getSize();
        if ($productCount > 0) {
            Mage::throwException(Mage::helper('Mage_Tax_Helper_Data')->__('You cannot delete this tax class as it is used for %d products.', $productCount));
        }
    }

    /**
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Mage_Tax::sales_tax_classes_product')
            ->_addBreadcrumb(Mage::helper('Mage_Tax_Helper_Data')->__('Sales'), Mage::helper('Mage_Tax_Helper_Data')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('Mage_Tax_Helper_Data')->__('Tax'), Mage::helper('Mage_Tax_Helper_Data')->__('Tax'))
            ->_addBreadcrumb(Mage::helper('Mage_Tax_Helper_Data')->__('Manage Product Tax Classes'), Mage::helper('Mage_Tax_Helper_Data')->__('Manage Product Tax Classes'))
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
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Tax::classes_product');
    }

}
