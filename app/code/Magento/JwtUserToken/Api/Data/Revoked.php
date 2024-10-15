<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Api\Data;

use Magento\Framework\DataObject;

/**
 * Revoked token data.
 */
class Revoked extends DataObject
{
    /**
     * @param int $userTypeId
     * @param int $userId
     * @param int $beforeTimestamp
     * @param array $data
     */
    public function __construct(?int $userTypeId, ?int $userId, ?int $beforeTimestamp, array $data = [])
    {
        if ($userTypeId !== null) {
            $data['user_type_id'] = $userTypeId;
        }
        if ($userId !== null) {
            $data['user_id'] = $userId;
        }
        if ($beforeTimestamp !== null) {
            $data['revoke_before'] = $beforeTimestamp;
        }

        parent::__construct($data);
    }

    public function getUserTypeId(): int
    {
        return (int) $this->getData('user_type_id');
    }

    public function getUserId(): int
    {
        return (int) $this->getData('user_id');
    }

    public function getBeforeTimestamp(): int
    {
        return (int) $this->getData('revoke_before');
    }
}
