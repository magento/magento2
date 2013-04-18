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
class Mage_Webhook_Adminhtml_Webhook_RegistrationController extends Mage_Adminhtml_Controller_Action
{

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
     * Activate subscriber
     * Step 1 - display subscriber required resources
     *
     * @return void
     */
    public function activateAction()
    {
        $subscriber = $this->_initSubscriber();

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Agree to provide required subscriber resources
     * Step 2 - redirect to specified auth action
     */
    public function acceptAction()
    {
        $subscriber = $this->_initSubscriber();

        $route = '*/webhook_registration/create_api_user';

        $this->_redirect($route, array('id' => $subscriber->getId()));
    }

    /**
     * Start createApiUser
     */
    public function create_api_userAction()
    {
        $subscriber = $this->_initSubscriber();
        $user = $subscriber->getApiUser();

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Continue createApiUser
     */
    public function registerAction()
    {
        try {
            /** @var Mage_Webhook_Model_Subscriber $subscriber */
            $subscriber = $this->_initSubscriber();

            $key = $this->getRequest()->getParam('apikey');
            $secret = $this->getRequest()->getParam('apisecret');
            $email = $this->getRequest()->getParam('email');
            $company = $this->getRequest()->getParam('company');

            if (empty($key) || empty($secret) || empty($email)) {
                throw Mage::exception('Mage_Webhook', $this->__('API Key, API Secret and Contact Email are required fields.'));
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->_redirectFailed($this->__('Invalid Email address provided'));
                return;
            }

            $subscriber->createUserAndRole($email, $key, $secret, $company)
            /* temporary solution in order not to catch an error */
                ->setIsNewlyActivated(true)
                ->setStatus(Mage_Webhook_Model_Subscriber::STATUS_ACTIVE)
                ->save();

            $this->_redirectSucceeded($subscriber);

        } catch (Mage_Core_Exception $e) {
            $this->_redirectFailed($e->getMessage());

        } catch (Exception $e) {
            Mage::logException($e);
            $this->_redirectFailed($this->__('An unexpected error happened. Please try again.'));
        }
    }

    protected function _redirectSucceeded(Mage_Webhook_Model_Subscriber $subscriber)
    {
        $this->_getSession()->addSuccess($this->__("The subscriber '%s' has been activated.", $subscriber->getName()));
        $this->_redirect('*/webhook_registration/succeeded', array('id' => $subscriber->getId()));
    }

    protected function _redirectFailed($errorMessage)
    {
        $this->_getSession()->addError($errorMessage);
        $this->_redirect('*/webhook_registration/failed');
    }

    /**
     * Redirect to this page when the authentication process is completed successfully
     */
    public function succeededAction()
    {
        $this->loadLayout();
        $this->renderLayout();
        $subscriber = $this->_initSubscriber();
        $this->_getSession()->addSuccess($this->__("The subscriber '%s' has been activated.", $subscriber->getName()));
    }

    /**
     * Redirect to this page when the authentication process is failed by some reasons
     */
    public function failedAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}
