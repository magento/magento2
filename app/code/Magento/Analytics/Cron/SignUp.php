<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\Connector;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\FlagManager;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Cron class for the Advanced Reporting signup process
 */
class SignUp
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var WriterInterface
     */
    private $configWriter;

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
     * @param Connector $connector
     * @param WriterInterface $configWriter
     * @param FlagManager $flagManager
     * @param ReinitableConfigInterface $reinitableConfig
     */
    public function __construct(
        Connector $connector,
        WriterInterface $configWriter,
        FlagManager $flagManager,
        ReinitableConfigInterface $reinitableConfig
    ) {
        $this->connector = $connector;
        $this->configWriter = $configWriter;
        $this->flagManager = $flagManager;
        $this->reinitableConfig = $reinitableConfig;
    }

    /**
     * Execute scheduled subscription operation.
     *
     * In case of failure writes message to notifications inbox
     *
     * @return bool
     * @throws NotFoundException
     */
    public function execute()
    {
        $attemptsCount = (int)$this->flagManager->getFlagData(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);

        if ($attemptsCount <= 0) {
            $this->deleteAnalyticsCronExpr();
            $this->flagManager->deleteFlag(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
            return false;
        }

        $attemptsCount--;
        $this->flagManager->saveFlag(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $attemptsCount);
        $signUpResult = $this->connector->execute('signUp');
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
