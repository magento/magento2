<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller\Ajax;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;

/**
 * Login controller
 *
 * @method \Zend_Controller_Request_Http getRequest()
 * @method \Magento\Framework\App\Response\Http getResponse()
 */
class Login extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Core\Helper\Data $helper
     */
    protected $helper;

    /**
     * Initialize Login controller
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Core\Helper\Data $helper
     * @param AccountManagementInterface $customerAccountManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Core\Helper\Data $helper,
        AccountManagementInterface $customerAccountManagement
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->customerAccountManagement = $customerAccountManagement;
    }

    /**
     * Login registered users and initiate a session.
     *
     * Expects a POST. ex for JSON {"username":"user@magento.com", "password":"userpassword"}
     *
     * @return void
     */
    public function execute()
    {
        $credentials = null;
        $httpBadRequestCode = 400;
        $httpUnauthorizedCode = 401;

        try {
            $credentials = $this->helper->jsonDecode($this->getRequest()->getRawBody());
        } catch (\Exception $e) {
            $this->getResponse()->setHttpResponseCode($httpBadRequestCode);
            return;
        }
        if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setHttpResponseCode($httpBadRequestCode);
            return;
        }
        $responseText = null;
        try {
            $customer = $this->customerAccountManagement->authenticate(
                $credentials['username'],
                $credentials['password']
            );
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $this->customerSession->regenerateId();
        } catch (EmailNotConfirmedException $e) {
            $responseText = $e->getMessage();
        } catch (InvalidEmailOrPasswordException $e) {
            $responseText = $e->getMessage();
        } catch (\Exception $e) {
            $responseText = __('There was an error validating the username and password.');
        }
        if ($responseText) {
            $this->getResponse()->setHttpResponseCode($httpUnauthorizedCode);
        } else {
            $responseText = __('Login successful.');
        }
        $this->getResponse()->representJson($this->helper->jsonEncode(['message' => $responseText]));
    }
}
