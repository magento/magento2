<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview;

/**
 * Extended Interface of \Magento\Framework\Mview\ViewInterface
 */
interface ViewSubscriptionInterface
{
    /**
     * Initializes Subscription instance
     *
     * @param array $subscriptionConfig
     * @return \Magento\Framework\Mview\View\SubscriptionInterface
     */
    public function initSubscriptionInstance(array $subscriptionConfig);
}
