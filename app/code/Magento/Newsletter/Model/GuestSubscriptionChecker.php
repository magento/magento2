<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Checks guest subscription by email.
 */
class GuestSubscriptionChecker
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ResourceConnection $resourceConnection, StoreManagerInterface $storeManager)
    {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
    }

    /**
     * Check is subscribed by email
     *
     * @param string $subscriberEmail
     * @return bool
     */
    public function isSubscribed(string $subscriberEmail): bool
    {
        if (!empty($subscriberEmail)) {
            $storeIds = $this->storeManager->getWebsite()->getStoreIds();
            $connection = $this->resourceConnection->getConnection();
            $select = $connection
                ->select()
                ->from($this->resourceConnection->getTableName('newsletter_subscriber'))
                ->where('subscriber_email = ?', $subscriberEmail)
                ->where('store_id IN (?)', $storeIds)
                ->where('customer_id = 0')
                ->limit(1);

            return (bool)$connection->fetchOne($select);
        }

        return false;
    }
}
