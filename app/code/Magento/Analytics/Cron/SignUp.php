<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\AnalyticsConnector;
use Magento\Analytics\Model\Config\Backend\Enabled;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Psr\Log\LoggerInterface;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\AdminNotification\Model\ResourceModel\Inbox as InboxResource;
use Magento\Analytics\Model\FlagManager;

/**
 * Class SignUp
 */
class SignUp
{
    /**
     * @var AnalyticsConnector
     */
    private $analyticsConnector;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var InboxFactory
     */
    private $inboxFactory;

    /**
     * @var InboxResource
     */
    private $inboxResource;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * SignUp constructor.
     * @param AnalyticsConnector $analyticsConnector
     * @param WriterInterface $configWriter
     * @param LoggerInterface $logger
     * @param InboxFactory $inboxFactory
     * @param InboxResource $inboxResource
     * @param FlagManager $flagManager
     */
    public function __construct(
        AnalyticsConnector $analyticsConnector,
        WriterInterface $configWriter,
        LoggerInterface $logger,
        InboxFactory $inboxFactory,
        InboxResource $inboxResource,
        FlagManager $flagManager
    ) {
        $this->analyticsConnector = $analyticsConnector;
        $this->configWriter = $configWriter;
        $this->logger = $logger;
        $this->inboxFactory = $inboxFactory;
        $this->inboxResource = $inboxResource;
        $this->flagManager = $flagManager;
    }

    /**
     * Method executes by cron
     * @return bool
     */
    public function execute()
    {
        $attemptsCount = $this->flagManager->getFlagData(Enabled::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
        if ($attemptsCount === null) {
            $this->deleteAnalyticsCronExpr();
            return false;
        }

        if ($attemptsCount <= 0) {
            $this->deleteAnalyticsCronExpr();
            $this->flagManager->delete(Enabled::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
            $inboxNotification = $this->inboxFactory->create();
            $inboxNotification->addNotice(
                "Analytics subscription error",
                "Analytics subscription error"
            );
            $this->inboxResource->save($inboxNotification);
            return false;
        }
        $attemptsCount -= 1;
        $this->flagManager->updateFlag(Enabled::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $attemptsCount);
        $generateTokenResult = $this->analyticsConnector->execute('generateTokenCommand');
        if ($generateTokenResult === false) {
            $this->writeErrorLog("The attempt of subscription was unsuccessful on step generate token.");
            return false;
        }

        $signUpResult = $this->analyticsConnector->execute('signUp');
        if ($signUpResult === false) {
            $this->writeErrorLog("The attempt of subscription was unsuccessful on step sign-up.");
            return false;
        }

        $this->deleteAnalyticsCronExpr();
        $this->flagManager->deleteFlag(Enabled::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
        return true;
    }

    /**
     * Delete Analytics cron config
     * @return void
     */
    private function deleteAnalyticsCronExpr()
    {
        $this->configWriter->delete(Enabled::CRON_STRING_PATH);
    }

    /**
     * Write Error Log
     * @param string $value
     * @return void
     */
    private function writeErrorLog($value)
    {
        $this->logger->warning($value);
    }
}