<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\AnalyticsConnector;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
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
     * Reinitable Config Model.
     *
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * SignUp constructor.
     *
     * @param AnalyticsConnector $analyticsConnector
     * @param WriterInterface $configWriter
     * @param InboxFactory $inboxFactory
     * @param InboxResource $inboxResource
     * @param FlagManager $flagManager
     * @param ReinitableConfigInterface $reinitableConfig
     */
    public function __construct(
        AnalyticsConnector $analyticsConnector,
        WriterInterface $configWriter,
        InboxFactory $inboxFactory,
        InboxResource $inboxResource,
        FlagManager $flagManager,
        ReinitableConfigInterface $reinitableConfig
    ) {
        $this->analyticsConnector = $analyticsConnector;
        $this->configWriter = $configWriter;
        $this->inboxFactory = $inboxFactory;
        $this->inboxResource = $inboxResource;
        $this->flagManager = $flagManager;
        $this->reinitableConfig = $reinitableConfig;
    }

    /**
     * Execute scheduled subscription operation
     * In case of failure writes message to notifications inbox
     *
     * @return bool
     */
    public function execute()
    {
        $attemptsCount = $this->flagManager->getFlagData(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
        if ($attemptsCount === null) {
            $this->deleteAnalyticsCronExpr();
            return false;
        }

        if ($attemptsCount <= 0) {
            $this->deleteAnalyticsCronExpr();
            $this->flagManager->deleteFlag(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
            $inboxNotification = $this->inboxFactory->create();
            $inboxNotification->addNotice(
                "Analytics subscription unsuccessful",
                "Analytics subscription unsuccessful"
            );
            $this->inboxResource->save($inboxNotification);
            return false;
        }

        $attemptsCount -= 1;
        $this->flagManager->saveFlag(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $attemptsCount);
        $signUpResult = $this->analyticsConnector->execute('signUp');
        if ($signUpResult === false) {
            return false;
        }

        $this->deleteAnalyticsCronExpr();
        $this->flagManager->deleteFlag(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
        return true;
    }

    /**
     * Delete cron schedule setting into config.
     *
     * Delete cron schedule setting for subscription handler into config and
     * re-initialize config cache to avoid auto-generate new schedule items.
     *
     * @return bool
     */
    private function deleteAnalyticsCronExpr()
    {
        $this->configWriter->delete(SubscriptionHandler::CRON_STRING_PATH);
        $this->reinitableConfig->reinit();
        return true;
    }
}
