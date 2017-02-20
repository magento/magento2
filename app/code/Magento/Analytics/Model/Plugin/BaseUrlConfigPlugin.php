<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Plugin;

use Magento\Analytics\Model\FlagManager;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Class BaseUrlConfigPlugin
 *
 * Plugin checks if value changed than save old base url and call subscription update.
 */
class BaseUrlConfigPlugin
{
    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var SubscriptionStatusProvider
     */
    private $subscriptionStatusProvider;

    /**
     * Config path for schedule setting of subscription handler.
     */
    const UPDATE_CRON_STRING_PATH = "crontab/default/jobs/analytics_update/schedule/cron_expr";

    /**
     * Represents a flag code for analytics old base url.
     */
    const OLD_BASE_URL_FLAG_CODE = 'analytics_old_base_url';
    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @param FlagManager $flagManager
     * @param SubscriptionStatusProvider $subscriptionStatusProvider
     * @param WriterInterface $configWriter
     */
    public function __construct(
        FlagManager $flagManager,
        SubscriptionStatusProvider $subscriptionStatusProvider,
        WriterInterface $configWriter
    ) {
        $this->flagManager = $flagManager;
        $this->subscriptionStatusProvider = $subscriptionStatusProvider;
        $this->configWriter = $configWriter;
    }

    /**
     * Invalidate WebApi cache if needed.
     *
     * @param \Magento\Framework\App\Config\Value $subject
     * @param \Magento\Framework\App\Config\Value $result
     * @return \Magento\Framework\App\Config\Value
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterSave(
        \Magento\Framework\App\Config\Value $subject,
        \Magento\Framework\App\Config\Value $result
    ) {
        if ($result->isValueChanged()
            && $this->subscriptionStatusProvider->getStatus() === SubscriptionStatusProvider::ENABLED
        ) {
            $this->flagManager->saveFlag(static::OLD_BASE_URL_FLAG_CODE, $result->getOldValue());
            $this->setCronSchedule();
        }

        return $result;
    }

    /**
     * Set cron schedule setting into config for activation of update process.
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

        $this->configWriter->save(self::UPDATE_CRON_STRING_PATH, $cronExprString);

        return true;
    }
}
