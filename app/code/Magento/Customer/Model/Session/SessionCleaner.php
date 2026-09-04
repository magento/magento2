<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
        ?CustomerResourceModel $customerResourceModel = null,
        ?VisitorResourceModel $visitorResourceModel = null
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
        $visitorData = $this->sessionManager->getVisitorData();
        if ($visitorData !== null) {
            if (isset($visitorData['visitor_id'])) {
                $this->visitorResourceModel->updateCreatedAt((int) $visitorData['visitor_id'], $timestamp + 1);
            }
            $this->clearCustomerDataFromVisitorSession($visitorData);
        }
    }

    /**
     * Clear authenticated customer linkage from visitor session payload.
     *
     * @param array $visitorData
     * @return void
     */
    private function clearCustomerDataFromVisitorSession(array $visitorData): void
    {
        $visitorData['customer_id'] = null;
        $visitorData['do_customer_login'] = false;
        $visitorData['do_customer_logout'] = false;
        $this->sessionManager->setVisitorData($visitorData);
    }
}
