<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Newsletter subscribe controller
 */
abstract class Subscriber extends \Magento\Framework\App\Action\Action
{
    /**
     * Configuration path to newsletter active setting
     */
    private const XML_PATH_NEWSLETTER_ACTIVE = 'newsletter/general/active';

    /**
     * Customer session
     *
     * @var Session
     */
    protected $_customerSession;

    /**
     * Subscriber factory
     *
     * @var SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CustomerUrl
     */
    protected $_customerUrl;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerUrl $customerUrl
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        ScopeConfigInterface $scopeConfig = null,
        UrlInterface $url = null
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_customerSession = $customerSession;
        $this->_customerUrl = $customerUrl;
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->url = $url ?: ObjectManager::getInstance()->get(UrlInterface::class);
    }

    /**
     * Check customer authentication for some actions
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->scopeConfig->getValue(
            self::XML_PATH_NEWSLETTER_ACTIVE,
            ScopeInterface::SCOPE_STORE
        )
        ) {
            $defaultNoRouteUrl = $this->scopeConfig->getValue(
                'web/default/no_route',
                ScopeInterface::SCOPE_STORE
            );

            $redirectUrl = $this->url->getUrl($defaultNoRouteUrl);
            return $this->resultRedirectFactory
                        ->create()
                        ->setUrl($redirectUrl);
        }

        return parent::dispatch($request);
    }
}
