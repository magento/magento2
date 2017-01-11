<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Config\Backend\Enabled;

use Magento\Analytics\Model\FlagManager;
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
    const CRON_STRING_PATH = 'crontab/default/jobs/analytics_generate/schedule/cron_expr';

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
     * @param WriterInterface $configWriter
     * @param FlagManager $flagManager
     */
    public function __construct(
        WriterInterface $configWriter,
        FlagManager $flagManager
    ) {
        $this->configWriter = $configWriter;
        $this->flagManager = $flagManager;
    }

    /**
     * Performs change subscription environment on config value change.
     *
     * @param Value $configValue
     *
     * @return bool
     */
    public function process(Value $configValue)
    {
        if ($configValue->isValueChanged()) {

            $enabled = $configValue->getData('value');

            if ($enabled) {
                $this->setCronSchedule();
                $this->setAttemptsFlag();
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
