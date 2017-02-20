<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Plugin;

use Magento\Analytics\Model\FlagManager;
use Magento\Analytics\Model\SubscriptionStatusProvider;

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
     * Represents a flag code for analytics old base url.
     */
    const OLD_BASE_URL_FLAG_CODE = 'analytics_old_base_url';

    /**
     * @param FlagManager $flagManager
     * @param SubscriptionStatusProvider $subscriptionStatusProvider
     */
    public function __construct(FlagManager $flagManager, SubscriptionStatusProvider $subscriptionStatusProvider)
    {
        $this->flagManager = $flagManager;
        $this->subscriptionStatusProvider = $subscriptionStatusProvider;
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
        if (
            $result->isValueChanged()
            && $this->subscriptionStatusProvider->getStatus() === SubscriptionStatusProvider::ENABLED
        ) {
            $this->flagManager->saveFlag(static::OLD_BASE_URL_FLAG_CODE, $result->getOldValue());
        }

        return $result;
    }
}
