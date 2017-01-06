<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\AnalyticsConnector;
use Magento\Framework\FlagFactory;
use Magento\Analytics\Model\Config\Backend\Enabled;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Flag\FlagResource;
use Psr\Log\LoggerInterface;
use Magento\Framework\Flag;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\AdminNotification\Model\ResourceModel\Inbox as InboxResource;

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
     * @var FlagFactory
     */
    private $flagFactory;
    /**
     * @var WriterInterface
     */
    private $configWriter;
    /**
     * @var FlagResource
     */
    private $flagResource;
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
     * SignUp constructor.
     * @param AnalyticsConnector $analyticsConnector
     */
    public function __construct(
        AnalyticsConnector $analyticsConnector,
        FlagFactory $flagFactory,
        WriterInterface $configWriter,
        FlagResource $flagResource,
        LoggerInterface $logger,
        InboxFactory $inboxFactory,
        InboxResource $inboxResource
    ) {
        $this->analyticsConnector = $analyticsConnector;
        $this->flagFactory = $flagFactory;
        $this->configWriter = $configWriter;
        $this->flagResource = $flagResource;
        $this->logger = $logger;
        $this->inboxFactory = $inboxFactory;
        $this->inboxResource = $inboxResource;
    }

    /**
     * Method executes by cron
     * @return bool
     */
    public function execute()
    {
        $attemptsFlag = $this->flagFactory
            ->create(['data' => ['flag_code' => Enabled::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE]])
            ->loadSelf();

        if ($attemptsFlag->getFlagData() === null) {
            $this->deleteAnalyticsCronExpr();
            return false;
        }

        if ($attemptsFlag->getFlagData() <= 0) {
            $this->deleteAnalyticsCronExpr();
            $this->deleteFlag($attemptsFlag);
            $inboxNotification = $this->inboxFactory->create();
            $inboxNotification->addNotice(
                "Analytics subscription error",
                "Analytics subscription error"
            );
            $this->inboxResource->save($inboxNotification);
            return false;
        }
        $this->decrementFlag($attemptsFlag);
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
        $this->deleteFlag($attemptsFlag);
        return true;
    }

    /**
     * Decrement attempts flag value
     * @param Flag $attemptsFlag
     * @return void
     */
    private function decrementFlag($attemptsFlag)
    {
        $attemptsFlag->setFlagData($attemptsFlag->getFlagData() - 1);
        $this->flagResource->save($attemptsFlag);
    }

    /**
     * Delete Analytics attempts flag
     * @param Flag $attemptsFlag
     * @return void
     */
    private function deleteFlag($attemptsFlag)
    {
        $this->flagResource->delete($attemptsFlag);
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