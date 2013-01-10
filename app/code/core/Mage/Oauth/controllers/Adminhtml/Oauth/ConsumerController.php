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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Manage consumers controller
 *
 * @category    Mage
 * @package     Mage_Oauth
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Oauth_Adminhtml_Oauth_ConsumerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Perform layout initialization actions
     *
     * @return Mage_Oauth_Adminhtml_Oauth_ConsumerController
     */
    protected function  _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Mage_Oauth::system_api_oauth_consumer');
        return $this;
    }
    /**
     * Unset unused data from request
     * Skip getting "key" and "secret" because its generated from server side only
     *
     * @param array $data
     * @return array
     */
    protected function _filter(array $data)
    {
        foreach (array('id', 'back', 'form_key', 'key', 'secret') as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }
        return $data;
    }

    /**
     * Init titles
     *
     * @return Mage_Oauth_Adminhtml_Oauth_ConsumerController
     */
    public function preDispatch()
    {
        $this->_title($this->__('System'))
            ->_title($this->__('OAuth'))
            ->_title($this->__('Consumers'));
        parent::preDispatch();
        return $this;
    }

    /**
     * Render grid page
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Render grid AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create page action
     */
    public function newAction()
    {
        /** @var $model Mage_Oauth_Model_Consumer */
        $model = Mage::getModel('Mage_Oauth_Model_Consumer');

        $formData = $this->_getFormData();
        if ($formData) {
            $this->_setFormData($formData);
            $model->addData($formData);
        } else {
            /** @var $helper Mage_Oauth_Helper_Data */
            $helper = Mage::helper('Mage_Oauth_Helper_Data');
            $model->setKey($helper->generateConsumerKey());
            $model->setSecret($helper->generateConsumerSecret());
            $this->_setFormData($model->getData());
        }

        Mage::register('current_consumer', $model);

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Edit page action
     */
    public function editAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (!$id) {
            $this->_getSession()->addError(Mage::helper('Mage_Oauth_Helper_Data')->__('Invalid ID parameter.'));
            $this->_redirect('*/*/index');
            return;
        }

        /** @var $model Mage_Oauth_Model_Consumer */
        $model = Mage::getModel('Mage_Oauth_Model_Consumer');
        $model->load($id);

        if (!$model->getId()) {
            $this->_getSession()->addError(Mage::helper('Mage_Oauth_Helper_Data')->__('Entry with ID #%s not found.', $id));
            $this->_redirect('*/*/index');
            return;
        }

        $model->addData($this->_filter($this->getRequest()->getParams()));
        Mage::register('current_consumer', $model);

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Render edit page
     */
    public function saveAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$this->_validateFormKey()) {
            if ($id) {
                $this->_redirect('*/*/edit', array('id' => $id));
            } else {
                $this->_redirect('*/*/new', array('id' => $id));
            }
            return;
        }

        $data = $this->_filter($this->getRequest()->getParams());

        /** @var $model Mage_Oauth_Model_Consumer */
        $model = Mage::getModel('Mage_Oauth_Model_Consumer');

        if ($id) {
            if (!(int) $id) {
                $this->_getSession()->addError(
                    $this->__('Invalid ID parameter.'));
                $this->_redirect('*/*/index');
                return;
            }
            $model->load($id);

            if (!$model->getId()) {
                $this->_getSession()->addError(
                    $this->__('Entry with ID #%s not found.', $id));
                $this->_redirect('*/*/index');
                return;
            }
        } else {
            $dataForm = $this->_getFormData();
            if ($dataForm) {
                $data['key']    = $dataForm['key'];
                $data['secret'] = $dataForm['secret'];
            } else {
                // If an admin was started create a new consumer and at this moment he has been edited an existing
                // consumer, we save the new consumer with a new key-secret pair
                /** @var $helper Mage_Oauth_Helper_Data */
                $helper = Mage::helper('Mage_Oauth_Helper_Data');

                $data['key']    = $helper->generateConsumerKey();
                $data['secret'] = $helper->generateConsumerSecret();
            }
        }

        try {
            $model->addData($data);
            $model->save();
            $this->_getSession()->addSuccess($this->__('The consumer has been saved.'));
            $this->_setFormData(null);
        } catch (Mage_Core_Exception $e) {
            $this->_setFormData($data);
            $this->_getSession()->addError(Mage::helper('Mage_Core_Helper_Data')->escapeHtml($e->getMessage()));
            $this->getRequest()->setParam('back', 'edit');
        } catch (Exception $e) {
            $this->_setFormData(null);
            Mage::logException($e);
            $this->_getSession()->addError($this->__('An error occurred on saving consumer data.'));
        }

        if ($this->getRequest()->getParam('back')) {
            if ($id || $model->getId()) {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
            } else {
                $this->_redirect('*/*/new');
            }
        } else {
            $this->_redirect('*/*/index');
        }
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $action = $this->getRequest()->getActionName();
        $resourceId = null;
        switch ($action) {
            case 'delete':
                $resourceId = 'Mage_Oauth::consumer_delete';
                break;

            case 'new':
            case 'save':
                $resourceId = 'Mage_Oauth::consumer_edit';
                break;

            default:
                $resourceId = 'Mage_Oauth::consumer';
                break;
        }

        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed($resourceId);
    }

    /**
     * Get form data
     *
     * @return array
     */
    protected function _getFormData()
    {
        return $this->_getSession()->getData('consumer_data', true);
    }

    /**
     * Set form data
     *
     * @param $data
     * @return Mage_Oauth_Adminhtml_Oauth_ConsumerController
     */
    protected function _setFormData($data)
    {
        $this->_getSession()->setData('consumer_data', $data);
        return $this;
    }

    /**
     * Delete consumer action
     */
    public function deleteAction()
    {
        $consumerId = (int) $this->getRequest()->getParam('id');
        if ($consumerId) {
            try {
                /** @var $consumer Mage_Oauth_Model_Consumer */
                $consumer = Mage::getModel('Mage_Oauth_Model_Consumer')->load($consumerId);
                if (!$consumer->getId()) {
                    Mage::throwException(Mage::helper('Mage_Oauth_Helper_Data')->__('Unable to find a consumer.'));
                }

                $consumer->delete();

                $this->_getSession()->addSuccess(Mage::helper('Mage_Oauth_Helper_Data')->__('The consumer has been deleted.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e, Mage::helper('Mage_Oauth_Helper_Data')->__('An error occurred while deleting the consumer.')
                );
            }
        }
        $this->_redirect('*/*/index');
    }
}
