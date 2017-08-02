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
 * @since 2.2.0
 */
class NotificationAboutFailedSubscription implements MessageInterface
{
    /**
     * @var SubscriptionStatusProvider
     * @since 2.2.0
     */
    private $subscriptionStatusProvider;

    /**
     * @var UrlInterface
     * @since 2.2.0
     */
    private $urlBuilder;

    /**
     * @param SubscriptionStatusProvider $subscriptionStatusProvider
     * @param UrlInterface $urlBuilder
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getIdentity()
    {
        return hash('sha256', 'ANALYTICS_NOTIFICATION');
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function isDisplayed()
    {
        return $this->subscriptionStatusProvider->getStatus() === SubscriptionStatusProvider::FAILED;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }
}
