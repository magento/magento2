<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Config\Backend\Enabled;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\CollectionTime;
use Magento\Analytics\Model\NotificationTime;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\FlagManager;

/**
 * Class for processing of activation/deactivation MBI subscription.
 * @since 2.2.0
 */
class SubscriptionHandler
{
    /**
     * Flag code for reserve counter of attempts to subscribe.
     */
    const ATTEMPTS_REVERSE_COUNTER_FLAG_CODE = 'analytics_link_attempts_reverse_counter';

    /**
     * Config path for schedule setting of subscription handler.
     */
    const CRON_STRING_PATH = 'crontab/default/jobs/analytics_subscribe/schedule/cron_expr';

    /**
     * Max value for reserve counter of attempts to subscribe.
     *
     * @var int
     * @since 2.2.0
     */
    private $attemptsInitValue = 24;

    /**
     * Service which allows to write values into config.
     *
     * @var WriterInterface
     * @since 2.2.0
     */
    private $configWriter;

    /**
     * Flag Manager.
     *
     * @var FlagManager
     * @since 2.2.0
     */
    private $flagManager;

    /**
     * Model for handling Magento BI token value.
     *
     * @var AnalyticsToken
     * @since 2.2.0
     */
    private $analyticsToken;

    /**
     * Resource for managing last notification time about subscription to Magento Analytics.
     *
     * @var NotificationTime
     * @since 2.2.0
     */
    private $notificationTime;

    /**
     * @var ReinitableConfigInterface
     * @since 2.2.0
     */
    private $reinitableConfig;

    /**
     * @param WriterInterface $configWriter
     * @param FlagManager $flagManager
     * @param AnalyticsToken $analyticsToken
     * @param NotificationTime $notificationTime
     * @param ReinitableConfigInterface $reinitableConfig
     * @since 2.2.0
     */
    public function __construct(
        WriterInterface $configWriter,
        FlagManager $flagManager,
        AnalyticsToken $analyticsToken,
        NotificationTime $notificationTime,
        ReinitableConfigInterface $reinitableConfig
    ) {
        $this->configWriter = $configWriter;
        $this->flagManager = $flagManager;
        $this->analyticsToken = $analyticsToken;
        $this->notificationTime = $notificationTime;
        $this->reinitableConfig = $reinitableConfig;
    }

    /**
     * Processing of activation MBI subscription.
     *
     * Activate process of subscription handling if Analytics token is not received.
     *
     * @return bool
     * @since 2.2.0
     */
    public function processEnabled()
    {
        if (!$this->analyticsToken->isTokenExist()) {
            $this->setCronSchedule();
            $this->setAttemptsFlag();
            $this->notificationTime->unsetLastTimeNotificationValue();
            $this->reinitableConfig->reinit();
        }

        return true;
    }

    /**
     * Set cron schedule setting into config for activation of subscription process.
     *
     * @return bool
     * @since 2.2.0
     */
    private function setCronSchedule()
    {
        $cronExprArray = [
            '0',                    # Minute
            '*',                    # Hour
            '*',                    # Day of the Month
            '*',                    # Month of the Year
            '*',                    # Day of the Week
        ];

        $cronExprString = join(' ', $cronExprArray);

        $this->configWriter->save(self::CRON_STRING_PATH, $cronExprString);

        return true;
    }

    /**
     * Set flag as reserve counter of attempts subscription operation.
     *
     * @return bool
     * @since 2.2.0
     */
    private function setAttemptsFlag()
    {
        return $this->flagManager
            ->saveFlag(self::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $this->attemptsInitValue);
    }

    /**
     * Processing of deactivation MBI subscription.
     *
     * Disable data collection
     * and interrupt subscription handling if Analytics token is not received.
     *
     * @return bool
     * @since 2.2.0
     */
    public function processDisabled()
    {
        $this->disableCollectionData();

        if (!$this->analyticsToken->isTokenExist()) {
            $this->unsetAttemptsFlag();
        }

        return true;
    }

    /**
     * Unset flag of attempts subscription operation.
     *
     * @return bool
     * @since 2.2.0
     */
    private function unsetAttemptsFlag()
    {
        return $this->flagManager
            ->deleteFlag(self::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
    }

    /**
     * Unset schedule of collection data cron.
     *
     * @return bool
     * @since 2.2.0
     */
    private function disableCollectionData()
    {
        $this->configWriter->delete(CollectionTime::CRON_SCHEDULE_PATH);

        return true;
    }
}
