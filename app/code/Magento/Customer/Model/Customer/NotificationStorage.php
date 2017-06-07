<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Serialize\SerializerInterface;

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

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * NotificationStorage constructor.
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
            $this->getSerializer()->serialize([
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

    /**
     * Get serializer
     *
     * @return SerializerInterface
     * @deprecated
     */
    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(SerializerInterface::class);
        }
        return $this->serializer;
    }
}
