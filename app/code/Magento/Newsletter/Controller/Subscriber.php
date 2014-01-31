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
 * @category    Magento
 * @package     Magento_Newsletter
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Newsletter subscribe controller
 *
 * @category    Magento
 * @package     Magento_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Controller;

class Subscriber extends \Magento\App\Action\Action
{
    /**
     * Session
     *
     * @var \Magento\Core\Model\Session
     */
    protected $_session;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Customer factory
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * Subscriber factory
     *
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Core\Model\Session $session
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Core\Model\Session $session,
        \Magento\Core\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
        $this->_subscriberFactory = $subscriberFactory;
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        $this->_session = $session;
    }

    /**
     * New subscription action
     *
     * @throws \Magento\Core\Exception
     * @return void
     */
    public function newAction()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string) $this->getRequest()->getPost('email');

            try {
                if (!\Zend_Validate::is($email, 'EmailAddress')) {
                    throw new \Magento\Core\Exception(__('Please enter a valid email address.'));
                }

                if ($this->_objectManager->get('Magento\Core\Model\Store\Config')
                        ->getConfig(\Magento\Newsletter\Model\Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1
                    && !$this->_customerSession->isLoggedIn()) {
                    throw new \Magento\Core\Exception(__('Sorry, but the administrator denied subscription for guests. '
                        . 'Please <a href="%1">register</a>.',
                        $this->_objectManager->get('Magento\Customer\Helper\Data')->getRegisterUrl()));
                }

                $ownerId = $this->_customerFactory->create()
                        ->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($ownerId !== null && $ownerId != $this->_customerSession->getId()) {
                    throw new \Magento\Core\Exception(__('This email address is already assigned to another user.'));
                }

                $status = $this->_subscriberFactory->create()->subscribe($email);
                if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                    $this->messageManager->addSuccess(__('The confirmation request has been sent.'));
                } else {
                    $this->messageManager->addSuccess(__('Thank you for your subscription.'));
                }
            } catch (\Magento\Core\Exception $e) {
                $this->messageManager->addException($e, __('There was a problem with the subscription: %1',
                    $e->getMessage()));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong with the subscription.'));
            }
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }

    /**
     * Subscription confirm action
     * @return void
     */
    public function confirmAction()
    {
        $id    = (int) $this->getRequest()->getParam('id');
        $code  = (string) $this->getRequest()->getParam('code');

        if ($id && $code) {
            /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
            $subscriber = $this->_subscriberFactory->create()->load($id);

            if ($subscriber->getId() && $subscriber->getCode()) {
                if ($subscriber->confirm($code)) {
                    $this->messageManager->addSuccess(__('Your subscription has been confirmed.'));
                } else {
                    $this->messageManager->addError(__('This is an invalid subscription confirmation code.'));
                }
            } else {
                $this->messageManager->addError(__('This is an invalid subscription ID.'));
            }
        }

        $this->getResponse()->setRedirect($this->_storeManager->getStore()->getBaseUrl());
    }

    /**
     * Unsubscribe newsletter
     * @return void
     */
    public function unsubscribeAction()
    {
        $id    = (int) $this->getRequest()->getParam('id');
        $code  = (string) $this->getRequest()->getParam('code');

        if ($id && $code) {
            try {
                $this->_subscriberFactory->create()->load($id)
                    ->setCheckCode($code)
                    ->unsubscribe();
                $this->messageManager->addSuccess(__('You have been unsubscribed.'));
            } catch (\Magento\Core\Exception $e) {
                $this->messageManager->addException($e, $e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong with the un-subscription.'));
            }
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}
