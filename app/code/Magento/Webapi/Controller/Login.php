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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Webapi\Controller;

use Magento\Authz\Model\UserIdentifier;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Webapi\Exception;
use Magento\Webapi\Exception as HttpException;

/**
 * Login controller
 */
class Login extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var CustomerAccountServiceInterface
     */
    protected $customerAccountService;

    /**
     * @var \Magento\Webapi\Controller\Rest\Request\Deserializer\Factory
     */
    protected $deserializerFactory;

    /**
     * Initialize Login Service
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Webapi\Controller\Rest\Request\Deserializer\Factory $deserializerFactory
     * @param CustomerAccountServiceInterface $customerAccountService
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Session\Generic $session,
        \Magento\Webapi\Controller\Rest\Request\Deserializer\Factory $deserializerFactory,
        CustomerAccountServiceInterface $customerAccountService
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->deserializerFactory = $deserializerFactory;
        $this->customerAccountService = $customerAccountService;
    }

    /**
     * Login registered users and initiate a session. Send back the session id.
     *
     * Expects a POST. ex for JSON  {"username":"user@magento.com", "password":"userpassword"}
     *
     * @return void
     */
    public function indexAction()
    {
        $contentTypeHeaderValue = $this->getRequest()->getHeader('Content-Type');
        $contentType = $this->getContentType($contentTypeHeaderValue);
        $loginData = null;
        try {
            $loginData = $this->deserializerFactory
                ->get($contentType)
                ->deserialize($this->getRequest()->getRawBody());
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode($e->getCode());
            return;
        }
        if (!$loginData || $this->getRequest()->getMethod() !== \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_POST) {
            $this->getResponse()->setHttpResponseCode(HttpException::HTTP_BAD_REQUEST);
            return;
        }
        $customerData = null;
        try {
            $customerData = $this->customerAccountService->authenticate($loginData['username'], $loginData['password']);
        } catch (AuthenticationException $e) {
            $this->getResponse()->setHttpResponseCode(HttpException::HTTP_UNAUTHORIZED);
            return;
        }
        $this->session->start('frontend');
        $this->session->setUserId($customerData->getId());
        $this->session->setUserType(UserIdentifier::USER_TYPE_CUSTOMER);
        $this->session->regenerateId(true);
    }

    /**
     * Initiate a session for unregistered users. Send back the session id.
     *
     * @return void
     */
    public function anonymousAction()
    {
        $this->session->start('frontend');
        $this->session->setUserId(0);
        $this->session->setUserType(UserIdentifier::USER_TYPE_GUEST);
        $this->session->regenerateId(true);
    }

    /**
     * Get Content-Type value of request given the $header value.
     *
     * TODO: Remove this method if \Magento\Webapi\Controller\Rest\Request can be injected instead of
     * Magento\Framework\App\Request\Http which is injected by core di.xml
     *
     * @param string $headerValue
     * @return string
     * @throws \Magento\Webapi\Exception
     */
    protected function getContentType($headerValue)
    {
        if (!preg_match('~^([a-z\d/\-+.]+)(?:; *charset=(.+))?$~Ui', $headerValue, $matches)) {
            return null;
        }
        // request encoding check if it is specified in header
        if (isset($matches[2]) && \Magento\Webapi\Controller\Rest\Request::REQUEST_CHARSET != strtolower($matches[2])) {
            return null;
        }

        return $matches[1];
    }
}
