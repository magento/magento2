<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;

/**
 * Customers newsletter subscription controller
 */
abstract class Manage extends \Magento\Framework\App\Action\Action
{
    /**
     * Configuration path to newsletter active setting
     */
    const XML_PATH_NEWSLETTER_ACTIVE = 'newsletter/general/active';

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $url
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_scopeConfig = $scopeConfig;
        $this->_url = $url;
    }

    /**
     * Check customer authentication for some actions
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_scopeConfig->getValue(
            self::XML_PATH_NEWSLETTER_ACTIVE,
            ScopeInterface::SCOPE_STORE
        )
        ) {
            $defaultNoRouteUrl = $this->_scopeConfig->getValue(
                'web/default/no_route',
                ScopeInterface::SCOPE_STORE
            );

            $redirectUrl = $this->_url->getUrl($defaultNoRouteUrl);
            return $this->resultRedirectFactory
                        ->create()
                        ->setUrl($redirectUrl);
        }

        if (!$this->_customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }
}
