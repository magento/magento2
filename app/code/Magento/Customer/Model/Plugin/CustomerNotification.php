<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\State;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class CustomerNotification
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    /**
     * Cookie Manager
     *
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var State
     */
    private $state;

    /**
     * @param Session $session
     * @param NotificationStorage $notificationStorage
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        Session $session,
        NotificationStorage $notificationStorage,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        State $state
    ) {
        $this->session = $session;
        $this->notificationStorage = $notificationStorage;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->state = $state;
    }

    /**
     * @param AbstractAction $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(AbstractAction $subject, RequestInterface $request)
    {
        if (
            $this->state->getAreaCode() === Area::AREA_FRONTEND
            && $this->notificationStorage->isExists(
                NotificationStorage::UPDATE_CUSTOMER_SESSION,
                $this->session->getCustomerId()
            )
        ) {
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setDuration(315360000)
                ->setPath('/')
                ->setHttpOnly(false);
            $this->cookieManager->setPublicCookie(
                NotificationStorage::UPDATE_CUSTOMER_SESSION,
                '1',
                $publicCookieMetadata
            );
            $this->notificationStorage->remove(
                NotificationStorage::UPDATE_CUSTOMER_SESSION,
                $this->session->getCustomerId()
            );
            $this->cookieManager->deleteCookie(Http::COOKIE_VARY_STRING);
        }
    }
}
