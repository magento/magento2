<?php
/**
 *
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
namespace Magento\Backend\App\Action\Plugin;

class Authentication
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var string[]
     */
    protected $_openActions = array(
        'forgotpassword',
        'resetpassword',
        'resetpasswordpost',
        'logout',
        'refresh' // captcha refresh
    );

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_auth = $auth;
        $this->_url = $url;
        $this->_response = $response;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Magento\Backend\App\AbstractAction $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Backend\App\AbstractAction $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $requestedActionName = $request->getActionName();
        if (in_array($requestedActionName, $this->_openActions)) {
            $request->setDispatched(true);
        } else {
            if ($this->_auth->getUser()) {
                $this->_auth->getUser()->reload();
            }
            if (!$this->_auth->isLoggedIn()) {
                $this->_processNotLoggedInUser($request);
            } else {
                $this->_auth->getAuthStorage()->prolong();
            }
        }
        $this->_auth->getAuthStorage()->refreshAcl();
        return $proceed($request);
    }

    /**
     * Process not logged in user data
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     */
    protected function _processNotLoggedInUser(\Magento\Framework\App\RequestInterface $request)
    {
        $isRedirectNeeded = false;
        if ($request->getPost('login') && $this->_performLogin($request)) {
            $isRedirectNeeded = $this->_redirectIfNeededAfterLogin($request);
        }
        if (!$isRedirectNeeded && !$request->getParam('forwarded')) {
            if ($request->getParam('isIframe')) {
                $request->setParam(
                    'forwarded',
                    true
                )->setRouteName(
                    'adminhtml'
                )->setControllerName(
                    'auth'
                )->setActionName(
                    'deniedIframe'
                )->setDispatched(
                    false
                );
            } elseif ($request->getParam('isAjax')) {
                $request->setParam(
                    'forwarded',
                    true
                )->setRouteName(
                    'adminhtml'
                )->setControllerName(
                    'auth'
                )->setActionName(
                    'deniedJson'
                )->setDispatched(
                    false
                );
            } else {
                $request->setParam(
                    'forwarded',
                    true
                )->setRouteName(
                    'adminhtml'
                )->setControllerName(
                    'auth'
                )->setActionName(
                    'login'
                )->setDispatched(
                    false
                );
            }
        }
    }

    /**
     * Performs login, if user submitted login form
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    protected function _performLogin(\Magento\Framework\App\RequestInterface $request)
    {
        $outputValue = true;
        $postLogin = $request->getPost('login');
        $username = isset($postLogin['username']) ? $postLogin['username'] : '';
        $password = isset($postLogin['password']) ? $postLogin['password'] : '';
        $request->setPost('login', null);

        try {
            $this->_auth->login($username, $password);
        } catch (\Magento\Backend\Model\Auth\Exception $e) {
            if (!$request->getParam('messageSent')) {
                $this->messageManager->addError($e->getMessage());
                $request->setParam('messageSent', true);
                $outputValue = false;
            }
        }
        return $outputValue;
    }

    /**
     * Checks, whether Magento requires redirection after successful admin login, and redirects user, if needed
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    protected function _redirectIfNeededAfterLogin(\Magento\Framework\App\RequestInterface $request)
    {
        $requestUri = null;

        // Checks, whether secret key is required for admin access or request uri is explicitly set
        if ($this->_url->useSecretKey()) {
            $requestUri = $this->_url->getUrl('*/*/*', array('_current' => true));
        } elseif ($request) {
            $requestUri = $request->getRequestUri();
        }

        if (!$requestUri) {
            return false;
        }

        $this->_response->setRedirect($requestUri);
        $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
        return true;
    }
}
