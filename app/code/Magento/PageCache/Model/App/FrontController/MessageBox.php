<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\App\FrontController;

use Magento\Framework\App\FrontController;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class MessageBox
{
    /**
     * Name of cookie that holds private content version
     */
    const COOKIE_NAME = 'message_box_display';

    /**
     * Ten years cookie period
     */
    const COOKIE_PERIOD = 315360000;

    /**
     * Cookie manager
     *
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * Cookie metadata factory
     *
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * Message manager
     *
     * @var \Magento\Framework\Message\Manager
     */
    protected $messageManager;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\PageCache\Model\Config $config,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->request = $request;
        $this->config = $config;
        $this->messageManager = $messageManager;
    }

    /**
     * Set Cookie for msg box when it displays first
     *
     * @param FrontController $subject
     * @param \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface $result
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDispatch(FrontController $subject, $result)
    {
        if ($this->request->isPost() && $this->messageManager->hasMessages()) {
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setDuration(self::COOKIE_PERIOD)
                ->setPath('/')
                ->setHttpOnly(false);
            $this->cookieManager->setPublicCookie(self::COOKIE_NAME, 1, $publicCookieMetadata);
        }
        return $result;
    }
}
