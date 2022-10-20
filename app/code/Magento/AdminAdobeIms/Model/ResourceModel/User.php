<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\User\Model\ResourceModel\User as AdminUser;

class User extends AdminUser
{
    /**
     * Load data by specified email
     *
     * @param string $email
     * @return array
     * @throws LocalizedException
     */
    public function loadByEmail(string $email): array
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable())->where('email=:email');

        $binds = ['email' => $email];

        $result = $connection->fetchRow($select, $binds);

        if (!is_array($result)) {
            return [];
        }

        return $result;
    }
}
