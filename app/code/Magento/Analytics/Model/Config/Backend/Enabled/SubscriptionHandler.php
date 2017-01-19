<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Config\Backend\Enabled;

use Magento\Analytics\Model\FlagManager;
use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\NotificationTime;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;

/**
 * Add additional handling on config value change.
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
     */
    private $attemptsInitValue = 24;

    /**
     * Service which allows to write values into config.
     *
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * Flag Manager.
     *
     * @var FlagManager
     */
    private $flagManager;

    /**
     * Model for handling Magento BI token value.
     *
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
     * Resource for managing last notification time about subscription to Magento Analytics.
     *
     * @var NotificationTime
     */
    private $notificationTime;

    /**
     * @param WriterInterface $configWriter
     * @param FlagManager $flagManager
     * @param AnalyticsToken $analyticsToken
     * @param NotificationTime $notificationTime
     */
    public function __construct(
        WriterInterface $configWriter,
        FlagManager $flagManager,
        AnalyticsToken $analyticsToken,
        NotificationTime $notificationTime
    ) {
        $this->configWriter = $configWriter;
        $this->flagManager = $flagManager;
        $this->analyticsToken = $analyticsToken;
        $this->notificationTime = $notificationTime;
    }

    /**
     * Performs change subscription environment on config value change.
     *
     * Activate process of subscription handling
     * if subscription was activated and Analytics token has not been received
     * or interrupt subscription handling.
     *
     * @param Value $configValue
     *
     * @return bool
     */
    public function process(Value $configValue)
    {
        if ($configValue->isValueChanged() && !$this->analyticsToken->isTokenExist()) {
            $enabled = $configValue->getData('value');

            if ($enabled) {
                $this->setCronSchedule();
                $this->setAttemptsFlag();
                $this->notificationTime->unsetLastTimeNotificationValue();
            } else {
                $this->unsetAttemptsFlag();
            }
        }

        return true;
    }

    /**
     * Set cron schedule setting into config for activation of subscription process.
     *
     * @return bool
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
     */
    private function setAttemptsFlag()
    {
        return $this->flagManager
            ->saveFlag(self::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $this->attemptsInitValue);
    }

    /**
     * Unset flag of attempts subscription operation.
     *
     * @return bool
     */
    private function unsetAttemptsFlag()
    {
        return $this->flagManager
            ->deleteFlag(self::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
    }
}
