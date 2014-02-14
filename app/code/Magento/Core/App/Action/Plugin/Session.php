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

namespace Magento\Core\App\Action\Plugin;

class Session
{
    /**
     * @var \Magento\Core\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Session\SidResolverInterface
     */
    protected $_sidResolver;

    /**
     * @var \Magento\Stdlib\Cookie
     */
    protected $_cookie;

    /**
     * @var string[]
     */
    protected $_cookieCheckActions;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_url;

    /**
     * @var string
     */
    protected $_sessionNamespace;

    /**
     * @var \Magento\App\ActionFlag
     */
    protected $_flag;

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * @var \Magento\App\ResponseInterface
     */
    protected $_response;

    /**
     * @param \Magento\App\ActionFlag $flag
     * @param \Magento\App\ResponseInterface $response
     * @param \Magento\Core\Model\Session $session
     * @param \Magento\Stdlib\Cookie $cookie
     * @param \Magento\UrlInterface $url
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\Session\SidResolverInterface $sidResolver
     * @param string $sessionNamespace
     * @param string[] $cookieCheckActions
     */
    public function __construct(
        \Magento\App\ActionFlag $flag,
        \Magento\App\ResponseInterface $response,
        \Magento\Core\Model\Session $session,
        \Magento\Stdlib\Cookie $cookie,
        \Magento\UrlInterface $url,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\Session\SidResolverInterface $sidResolver,
        $sessionNamespace = '',
        array $cookieCheckActions = array()
    ) {
        $this->_session = $session;
        $this->_response = $response;
        $this->_sidResolver = $sidResolver;
        $this->_cookie = $cookie;
        $this->_cookieCheckActions = $cookieCheckActions;
        $this->_url = $url;
        $this->_sessionNamespace = $sessionNamespace;
        $this->_flag = $flag;
        $this->_storeConfig = $storeConfig;
    }

    /**
     * @param array $arguments
     * @param \Magento\Code\Plugin\InvocationChain $invocationChain
     * @return mixed
     */
    public function aroundDispatch(array $arguments = array(), \Magento\Code\Plugin\InvocationChain $invocationChain)
    {
        $request = $arguments[0];
        $checkCookie = in_array($request->getActionName(), $this->_cookieCheckActions)
            && !$request->getParam('nocookie', false);

        $cookies = $this->_cookie->get();

        if (empty($cookies)) {
            if ($this->_session->getCookieShouldBeReceived()) {
                $this->_session->unsCookieShouldBeReceived();
                if ($this->_storeConfig->getConfig('web/browser_capabilities/cookies')) {
                    $this->_forward($request);
                    return $this->_response;
                }
            } elseif ($checkCookie) {
                if ($request->getQuery($this->_sidResolver->getSessionIdQueryParam($this->_session), false)
                    && $this->_url->getUseSession()
                    && $this->_sessionNamespace != \Magento\Backend\App\AbstractAction::SESSION_NAMESPACE
                ) {
                    $this->_session->setCookieShouldBeReceived(true);
                } else {
                    $this->_forward($request);
                    return $this->_response;
                }
            }
        }
        return $invocationChain->proceed($arguments);
    }

    /**
     * Forward to noCookies action
     *
     * @param \Magento\App\RequestInterface $request
     * @return \Magento\App\RequestInterface
     */
    protected function _forward(\Magento\App\RequestInterface $request)
    {
        $request->initForward();
        $request->setActionName('noCookies');
        $request->setControllerName('index');
        $request->setModuleName('core');
        $request->setDispatched(false);
        return $request;
    }
}
