<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Plugin;

use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Framework\FlagManager;
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
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * Cron expression by next pattern:
     * # Minute # Hour # Day of the Month # Month of the Year # Day of the Week.
     *
     * @var string
     */
    private $cronExpression = '0 * * * *';

    /**
     * Config path for schedule setting of subscription handler.
     */
    const UPDATE_CRON_STRING_PATH = "crontab/default/jobs/analytics_update/schedule/cron_expr";

    /**
     * Represents a flag code for analytics old base url.
     */
    const OLD_BASE_URL_FLAG_CODE = 'analytics_old_base_url';

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
     * Sets update analytics cron job if base url was changed.
     *
     * @param \Magento\Config\Model\Config\Backend\Baseurl $subject
     * @param \Magento\Config\Model\Config\Backend\Baseurl $result
     * @return \Magento\Config\Model\Config\Backend\Baseurl
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterSave(
        \Magento\Config\Model\Config\Backend\Baseurl $subject,
        \Magento\Config\Model\Config\Backend\Baseurl $result
    ) {
        if (!$result->isValueChanged()) {
            return $result;
        }

        if ($this->isPluginApplicable($result)) {
            $this->flagManager->saveFlag(static::OLD_BASE_URL_FLAG_CODE, $result->getOldValue());
            $this->configWriter->save(self::UPDATE_CRON_STRING_PATH, $this->cronExpression);
        }

        return $result;
    }

    /**
     * @param \Magento\Config\Model\Config\Backend\Baseurl $result
     *
     * @return bool
     */
    private function isPluginApplicable(\Magento\Config\Model\Config\Backend\Baseurl $result)
    {
        return $result->getData('path') === \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL
            && $this->subscriptionStatusProvider->getStatus() === SubscriptionStatusProvider::ENABLED;
    }
}
