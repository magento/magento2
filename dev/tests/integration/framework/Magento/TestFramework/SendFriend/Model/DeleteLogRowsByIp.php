<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\SendFriend\Model;

use Magento\SendFriend\Model\ResourceModel\SendFriend as SendFriendResource;

/**
 * Delete log rows by ip address
 */
class DeleteLogRowsByIp
{
    /** @var SendFriendResource */
    private $sendFriendResource;

    /**
     * @param SendFriendResource $sendFriendResource
     */
    public function __construct(SendFriendResource $sendFriendResource)
    {
        $this->sendFriendResource = $sendFriendResource;
    }

    /**
     * Delete rows from sendfriend_log table by ip address
     *
     * @param string $ipAddress
     * @return void
     */
    public function execute(string $ipAddress): void
    {
        $connection = $this->sendFriendResource->getConnection();
        $condition = $connection->quoteInto('ip = ?', ip2long($ipAddress));
        $connection->delete($this->sendFriendResource->getMainTable(), $condition);
    }
}
