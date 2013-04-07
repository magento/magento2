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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Webhook_Adminhtml_Webhook_SubscriberController extends Mage_Adminhtml_Controller_Action
{
    const TEST = 'test';

    /**
     * Initialize general settings for subscriber
     *
     * @return Mage_Webhook_Model_Subscriber
     */
    protected function _initSubscriber()
    {
        $subscriber = Mage::getModel('Mage_Webhook_Model_Subscriber');
        $subscriberId = (int) $this->getRequest()->getParam('id');
        if ($subscriberId) {
            $subscriber->load($subscriberId);
        }
        Mage::register('current_subscriber', $subscriber);
        return $subscriber;
    }

    /**
     * Segments list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Mage_Webhook::system_api_webapi_webhook')
            ->_title(Mage::helper('Mage_Webhook_Helper_Data')->__('System'))
            ->_title(Mage::helper('Mage_Webhook_Helper_Data')->__('Web Services'))
            ->_title(Mage::helper('Mage_Webhook_Helper_Data')->__('WebHook Subscribers'));

        $this->renderLayout();
    }

    public function newAction()
    {
        Mage::register('webhook_action', 'new');
        $this->_forward('edit');
    }

    public function editAction()
    {
        $model  = $this->_initSubscriber();

        if ($model->getId()) {
            $data = Mage::getSingleton('Mage_Backend_Model_Session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('current_data', $model);
        }

        $this->loadLayout()
            ->_setActiveMenu('system/api')
            ->_title(Mage::helper('Mage_Webhook_Helper_Data')->__('System'))
            ->_title(Mage::helper('Mage_Webhook_Helper_Data')->__('Web Services'))
            ->_title(Mage::helper('Mage_Webhook_Helper_Data')->__('WebHook Subscribers'));
        if (Mage::registry('webhook_action') == 'new') {
            $this->_title(Mage::helper('Mage_Webhook_Helper_Data')->__('Add Subscriber'));
        } else {
            $this->_title(Mage::helper('Mage_Webhook_Helper_Data')->__('Edit Subscriber'));
        }

        $this->renderLayout();
    }

    /**
     * Save subscriber action
     *
     * @return void
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        /** @var $subscriber Mage_Webhook_Model_Subscriber */
        $subscriber = $this->_initSubscriber();
        if ($data) {
            $subscriber->addData($data)
                ->setTopics(isset($data['topics']) ? $data['topics'] : array());

            try {
                $subscriber->save();
                $this->_getSession()->addSuccess($this->__("The subscriber '%s' has been saved.", $subscriber->getName()));
            }
            catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }
        else {
            $this->_getSession()->addError($this->__("The subscriber '%s' has not been saved.", $subscriber->getName()));
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete subscriber action
     *
     * @return void
     */
    public function deleteAction()
    {
        $subscriber = $this->_initSubscriber();
        if ($subscriber && !$subscriber->getExtensionId()) {
            try {
                $subscriber->delete();
                $this->_getSession()->addSuccess($this->__("The subscriber '%s' has been removed.", $subscriber->getName()));
            }
            catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }
        else {
            $this->_getSession()->addError($this->__("The subscriber '%s' can not be removed.", $subscriber->getName()));
        }
        $this->_redirect('*/*/');
    }

    public function testAction()
    {
        $subscriber = $this->_initSubscriber();

        if (!$subscriber) {
            $this->_getSession()->addError($this->__('Unable to send message: Subscriber doesn\'t exist'));
        }
        else if (!$subscriber->isSubscribedToTopic(self::TEST)) {
            $this->_getSession()->addError($this->__('Unable to send message: Not subscribed to topic'));
        }
        else {
            $object = new Varien_Object(array('message' => $this->__('This is a test message.')));
            Mage::helper('Mage_Webhook_Helper_Data')->dispatchEvent(self::TEST, array('object' => $object));
            $this->_getSession()->addSuccess($this->__('Test message queued.'));
        }

        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
    }


    /**
     * Revoke subscriber
     *
     * @return void
     */
    public function revokeAction()
    {
        try {
            $subscriber = $this->_initSubscriber();
            $subscriber->setStatus(Mage_Webhook_Model_Subscriber::STATUS_REVOKED)
                ->save();

            // TODO: send 'deactivated' notification to the subscriber endpoint url

            $this->_getSession()->addSuccess($this->__("The subscriber '%s' has been revoked.", $subscriber->getName()));

        } catch (Mage_Webhook_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__("An unexpected error happened. Please try again."));
        }

        $this->_redirect('*/webhook_subscriber/index');
    }

    /**
     * Activate subscriber
     * Step 1 - display subscriber required resources
     *
     * @return void
     */
    public function activateAction()
    {
        $subscriber = $this->_initSubscriber();

        try {
            $subscriber->setStatus(Mage_Webhook_Model_Subscriber::STATUS_ACTIVE)
                ->save();

            // TODO: send 'deactivated' notification to the subscriber endpoint url

            $this->_getSession()->addSuccess($this->__("The subscriber '%s' has been activated.", $subscriber->getName()));

        } catch (Mage_Webhook_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__("An unexpected error happened. Please try again."));
        }

        $this->_redirect('*/webhook_subscriber/index');
    }
}
