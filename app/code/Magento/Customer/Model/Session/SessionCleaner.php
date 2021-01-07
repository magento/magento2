<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Session;

use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory as VisitorCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Session\Config;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\ScopeInterface;

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
     * @inheritdoc
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DateTimeFactory $dateTimeFactory,
        VisitorCollectionFactory $visitorCollectionFactory,
        SessionManagerInterface $sessionManager,
        SaveHandlerInterface $saveHandler
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->visitorCollectionFactory = $visitorCollectionFactory;
        $this->sessionManager = $sessionManager;
        $this->saveHandler = $saveHandler;
    }

    /**
     * @inheritdoc
     */
    public function clearFor(int $customerId): void
    {
        $sessionLifetime = $this->scopeConfig->getValue(
            Config::XML_PATH_COOKIE_LIFETIME,
            ScopeInterface::SCOPE_STORE
        );
        $dateTime = $this->dateTimeFactory->create();
        $activeSessionsTime = $dateTime->setTimestamp($dateTime->getTimestamp() - $sessionLifetime)
            ->format(DateTime::DATETIME_PHP_FORMAT);
        /** @var \Magento\Customer\Model\ResourceModel\Visitor\Collection $visitorCollection */
        $visitorCollection = $this->visitorCollectionFactory->create();
        $visitorCollection->addFieldToFilter('customer_id', $customerId);
        $visitorCollection->addFieldToFilter('last_visit_at', ['from' => $activeSessionsTime]);
        $visitorCollection->addFieldToFilter('session_id', ['neq' => $this->sessionManager->getSessionId()]);

        /** @var \Magento\Customer\Model\Visitor $visitor */
        foreach ($visitorCollection->getItems() as $visitor) {
            $sessionId = $visitor->getSessionId();
            $this->sessionManager->start();
            $this->saveHandler->destroy($sessionId);
            $this->sessionManager->writeClose();
        }
    }
}
