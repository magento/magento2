<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Connector;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\FlagManager;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Executes by cron schedule in case base url was changed
 */
class Update
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
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
     * @param Connector $connector
     * @param WriterInterface $configWriter
     * @param ReinitableConfigInterface $reinitableConfig
     * @param FlagManager $flagManager
     * @param AnalyticsToken $analyticsToken
     */
    public function __construct(
        Connector $connector,
        WriterInterface $configWriter,
        ReinitableConfigInterface $reinitableConfig,
        FlagManager $flagManager,
        AnalyticsToken $analyticsToken
    ) {
        $this->connector = $connector;
        $this->configWriter = $configWriter;
        $this->reinitableConfig = $reinitableConfig;
        $this->flagManager = $flagManager;
        $this->analyticsToken = $analyticsToken;
    }

    /**
     * Execute scheduled update operation
     *
     * @return bool
     * @throws NotFoundException
     */
    public function execute()
    {
        $result = false;
        $attemptsCount = (int)$this->flagManager
            ->getFlagData(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE);

        if (($attemptsCount > 0) && $this->analyticsToken->isTokenExist()) {
            $attemptsCount--;
            $this->flagManager
                ->saveFlag(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE, $attemptsCount);
            $result = $this->connector->execute('update');
        }

        if ($result || ($attemptsCount <= 0) || (!$this->analyticsToken->isTokenExist())) {
            $this->exitFromUpdateProcess();
        }

        return $result;
    }

    /**
     * Clean-up flags and refresh configuration
     */
    private function exitFromUpdateProcess(): void
    {
        $this->flagManager
            ->deleteFlag(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE);
        $this->flagManager->deleteFlag(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE);
        $this->configWriter->delete(SubscriptionUpdateHandler::UPDATE_CRON_STRING_PATH);
        $this->reinitableConfig->reinit();
    }
}
