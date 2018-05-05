<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\System\Message;

use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;

/**
 * Represents an analytics notification about failed subscription.
 */
class NotificationAboutFailedSubscription implements MessageInterface
{
    /**
     * @var SubscriptionStatusProvider
     */
    private $subscriptionStatusProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param SubscriptionStatusProvider $subscriptionStatusProvider
     * @param UrlInterface $urlBuilder
     */
    public function __construct(SubscriptionStatusProvider $subscriptionStatusProvider, UrlInterface $urlBuilder)
    {
        $this->subscriptionStatusProvider = $subscriptionStatusProvider;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function getIdentity()
    {
        return hash('sha256', 'ANALYTICS_NOTIFICATION');
    }

    /**
     * {@inheritdoc}
     */
    public function isDisplayed()
    {
        return $this->subscriptionStatusProvider->getStatus() === SubscriptionStatusProvider::FAILED;
    }

    /**
     * {@inheritdoc}
     */
    public function getText()
    {
        $messageDetails = '';

        $messageDetails .= __('Failed to synchronize data to the Magento Business Intelligence service. ');
        $messageDetails .= __(
            '<a href="%1">Retry Synchronization</a>',
            $this->urlBuilder->getUrl('analytics/subscription/retry')
        );

        return $messageDetails;
    }

    /**
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }
}
