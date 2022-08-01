<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Session;

use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\Customer\Model\ResourceModel\Visitor as VisitorResourceModel;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory as VisitorCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Deletes all session data which relates to customer, including current session data.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class SessionCleaner implements SessionCleanerInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var VisitorCollectionFactory
     */
    private $visitorCollectionFactory;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var SaveHandlerInterface
     */
    private $saveHandler;

    /**
     * @var CustomerResourceModel
     */
    private $customerResourceModel;

    /**
     * @var VisitorResourceModel
     */
    private $visitorResourceModel;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTimeFactory $dateTimeFactory
     * @param VisitorCollectionFactory $visitorCollectionFactory
     * @param SessionManagerInterface $sessionManager
     * @param SaveHandlerInterface $saveHandler
     * @param CustomerResourceModel|null $customerResourceModel
     * @param VisitorResourceModel|null $visitorResourceModel
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DateTimeFactory $dateTimeFactory,
        VisitorCollectionFactory $visitorCollectionFactory,
        SessionManagerInterface $sessionManager,
        SaveHandlerInterface $saveHandler,
        CustomerResourceModel $customerResourceModel = null,
        VisitorResourceModel $visitorResourceModel = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->visitorCollectionFactory = $visitorCollectionFactory;
        $this->sessionManager = $sessionManager;
        $this->saveHandler = $saveHandler;
        $this->customerResourceModel = $customerResourceModel
            ?: ObjectManager::getInstance()->get(CustomerResourceModel::class);
        $this->visitorResourceModel = $visitorResourceModel
            ?: ObjectManager::getInstance()->get(VisitorResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function clearFor(int $customerId): void
    {
        $dateTime = $this->dateTimeFactory->create();
        $timestamp = $dateTime->getTimestamp();
        $this->customerResourceModel->updateSessionCutOff($customerId, $timestamp);
        if ($this->sessionManager->getVisitorData() !== null) {
            $visitorId = $this->sessionManager->getVisitorData()['visitor_id'];
            $this->visitorResourceModel->updateCreatedAt((int) $visitorId, $timestamp + 1);
        }
    }
}
