<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer;

use Magento\Framework\Cache\FrontendInterface;

class NotificationStorage
{
    const UPDATE_CUSTOMER_SESSION = 'update_customer_session';

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @param FrontendInterface $cache
     */
    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Add notification in cache
     *
     * @param string $notificationType
     * @param string $customerId
     * @return void
     */
    public function add($notificationType, $customerId)
    {
        $this->cache->save(
            serialize([
                'customer_id' => $customerId,
                'notification_type' => $notificationType
            ]),
            $this->getCacheKey($notificationType, $customerId)
        );
    }

    /**
     * Check whether notification is exists in cache
     *
     * @param string $notificationType
     * @param string $customerId
     * @return bool
     */
    public function isExists($notificationType, $customerId)
    {
        return $this->cache->test($this->getCacheKey($notificationType, $customerId));
    }

    /**
     * Remove notification from cache
     *
     * @param string $notificationType
     * @param string $customerId
     * @return void
     */
    public function remove($notificationType, $customerId)
    {
        $this->cache->remove($this->getCacheKey($notificationType, $customerId));
    }

    /**
     * Retrieve cache key
     *
     * @param string $notificationType
     * @param string $customerId
     * @return string
     */
    private function getCacheKey($notificationType, $customerId)
    {
        return 'notification_' . $notificationType . '_' . $customerId;
    }
}
