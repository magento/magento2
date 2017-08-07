<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class \Magento\Customer\Model\Customer\NotificationStorage
 *
 * @since 2.1.0
 */
class NotificationStorage
{
    const UPDATE_CUSTOMER_SESSION = 'update_customer_session';

    /**
     * @var FrontendInterface
     * @since 2.1.0
     */
    private $cache;

    /**
     * @param FrontendInterface $cache
     */

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * NotificationStorage constructor.
     * @param FrontendInterface $cache
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
     */
    private function getCacheKey($notificationType, $customerId)
    {
        return 'notification_' . $notificationType . '_' . $customerId;
    }

    /**
     * Get serializer
     *
     * @return SerializerInterface
     * @deprecated 2.2.0
     * @since 2.2.0
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
