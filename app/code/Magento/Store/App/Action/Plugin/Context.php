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

namespace Magento\Store\App\Action\Plugin;

/**
 * Class ContextPlugin
 */
class Context
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\CookieManager
     */
    protected $cookieManager;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\App\Request\Http $httpRequest
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\CookieManager $cookieManager
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\App\Request\Http $httpRequest,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\CookieManager $cookieManager
    ) {
        $this->session      = $session;
        $this->httpContext  = $httpContext;
        $this->httpRequest  = $httpRequest;
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
    }

    /**
     * @param \Magento\Framework\App\Action\Action $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(
        \Magento\Framework\App\Action\Action $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->httpContext->setValue(
            \Magento\Core\Helper\Data::CONTEXT_CURRENCY,
            $this->session->getCurrencyCode(),
            $this->storeManager->getWebsite()->getDefaultStore()->getDefaultCurrency()->getCode()
        );

        $this->httpContext->setValue(
            \Magento\Core\Helper\Data::CONTEXT_STORE,
            $this->httpRequest->getParam(
                '___store',
                $this->cookieManager->getCookie(\Magento\Store\Model\Store::COOKIE_NAME)
            ),
            $this->storeManager->getWebsite()->getDefaultStore()->getCode()
        );
        return $proceed($request);
    }
}
