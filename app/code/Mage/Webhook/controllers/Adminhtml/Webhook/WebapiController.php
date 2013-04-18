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

/**
 * Controller for subscriber activation for Magento 2 API
 */
class Mage_Webhook_Adminhtml_Webhook_WebapiController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Start activation
     */
    public function activateAction()
    {
        $subscriber = $this->_initSubscriber();

        // TODO: refactor it !!!

        echo '<form action="' . $this->getUrl('*/*/register', array('id' => $subscriber->getId())) . '">
        <label for="company">Company Name</label>
        <input type="text" name="company"/><br>

        <label for="email">Contact Email</label>
        <input type="text" name="email"/><br>

        <label for="key">API Key</label>
        <input type="text" name="key"/><br>

        <label for="secret">API Secret</label>
        <input type="test" name="secret"/><br>
        <input type="submit" value="Submit">
        </form>';
    }

    /**
     * Continue activation
     */
    public function registerAction()
    {
        try {
            $subscriber = $this->_initSubscriber();

            $key = $this->getRequest()->getParam('key');
            $secret = $this->getRequest()->getParam('secret');
            $email = $this->getRequest()->getParam('email');
            $company = $this->getRequest()->getParam('company');

            if (empty($key) || empty($secret) || empty($email)) {
                throw Mage::exception('Mage_Webhook', $this->__("API Key, API Secret and Contact Email are required fields."));
            }

            $subscriber->createUserAndRole($subscriber, $email, $key, $secret, $company);
            /* temporary solution in order not to catch an error */
            $subscriber->setIsNewlyActivated(true);
            $subscriber->setStatus(Mage_Webhook_Model_Subscriber::STATUS_ACTIVE)
                ->save();

            $this->_redirectSucceeded($subscriber);

        } catch (Mage_Core_Exception $e) {
            $this->_redirectFailed($e->getMessage());

        } catch (Exception $e) {
            Mage::logException($e);
            $this->_redirectFailed($this->__("An unexpected error happened. Please try again.".$e));
        }
    }

    /**
     * Initialize general settings for subscriber
     *
     * @return Mage_Webhook_Model_Subscriber
     * @throws Mage_Webhook_Exception
     */
    protected function _initSubscriber()
    {
        $subscriber = Mage::getModel('Mage_Webhook_Model_Subscriber');
        $subscriberId = (int) $this->getRequest()->getParam('id');
        if ($subscriberId) {
            $subscriber->load($subscriberId);
        }

        if (!$subscriber->getId()) {
            throw Mage::exception('Mage_Webhook', $this->__("The subscriber is unknown."));
        }

        Mage::register('current_subscriber', $subscriber);

        return $subscriber;
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
}
