<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PasswordManagement\Model\Resource\Admin;

use Magento\User\Model\User as ModelUser;

/**
 * Admin user resource model
 */
class User extends \Magento\User\Model\Resource\User
{
    /**
     * Unlock specified user record(s)
     *
     * @param int|int[] $userIds
     * @return int number of affected rows
     */
    public function unlock($userIds)
    {
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }
        return $this->getConnection()->update(
            $this->getMainTable(),
            ['failures_num' => 0, 'first_failure' => null, 'lock_expires' => null],
            $this->getIdFieldName() . ' IN (' . $this->getConnection()->quote($userIds) . ')'
        );
    }

    /**
     * Lock specified user record(s)
     *
     * @param int|int[] $userIds
     * @param int $exceptId
     * @param int $lifetime
     * @return int number of affected rows
     */
    public function lock($userIds, $exceptId, $lifetime)
    {
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }
        $exceptId = (int)$exceptId;
        return $this->getConnection()->update(
            $this->getMainTable(),
            ['lock_expires' => $this->dateTime->formatDate(time() + $lifetime)],
            "{$this->getIdFieldName()} IN (" . $this->getConnection()->quote(
                $userIds
            ) . ")\n            AND {$this->getIdFieldName()} <> {$exceptId}"
        );
    }

    /**
     * Increment failures count along with updating lock expire and first failure dates
     *
     * @param ModelUser $user
     * @param int|false $setLockExpires
     * @param int|false $setFirstFailure
     * @return void
     */
    public function updateFailure($user, $setLockExpires = false, $setFirstFailure = false)
    {
        $update = ['failures_num' => new \Zend_Db_Expr('failures_num + 1')];
        if (false !== $setFirstFailure) {
            $update['first_failure'] = $this->dateTime->formatDate($setFirstFailure);
            $update['failures_num'] = 1;
        }
        if (false !== $setLockExpires) {
            $update['lock_expires'] = $this->dateTime->formatDate($setLockExpires);
        }
        $this->getConnection()->update(
            $this->getMainTable(),
            $update,
            $this->getConnection()->quoteInto("{$this->getIdFieldName()} = ?", $user->getId())
        );
    }

    /**
     * Purge and get remaining old password hashes
     *
     * @param ModelUser $user
     * @param int $retainLimit
     * @return array
     */
    public function getOldPasswords($user, $retainLimit = 4)
    {
        $userId = (int)$user->getId();
        $table = $this->getTable('admin_passwords');

        // purge expired passwords, except that should retain
        $retainPasswordIds = $this->getConnection()->fetchCol(
            $this->getConnection()->select()->from(
                $table,
                'password_id'
            )->where(
                'user_id = :user_id'
            )->order(
                'expires ' . \Magento\Framework\DB\Select::SQL_DESC
            )->order(
                'password_id ' . \Magento\Framework\DB\Select::SQL_DESC
            )->limit(
                $retainLimit
            ),
            [':user_id' => $userId]
        );
        $where = ['user_id = ?' => $userId, 'expires <= ?' => time()];
        if ($retainPasswordIds) {
            $where['password_id NOT IN (?)'] = $retainPasswordIds;
        }
        $this->getConnection()->delete($table, $where);

        // now get all remained passwords
        return $this->getConnection()->fetchCol(
            $this->getConnection()->select()->from($table, 'password_hash')->where('user_id = :user_id'),
            [':user_id' => $userId]
        );
    }

    /**
     * Remember a password hash for further usage
     *
     * @param ModelUser $user
     * @param string $passwordHash
     * @param int $lifetime
     * @return void
     */
    public function trackPassword($user, $passwordHash, $lifetime)
    {
        $now = time();
        $this->getConnection()->insert(
            $this->getTable('admin_passwords'),
            [
                'user_id' => $user->getId(),
                'password_hash' => $passwordHash,
                'expires' => $now + $lifetime,
                'last_updated' => $now
            ]
        );
    }

    /**
     * Get latest password for specified user id
     * Possible false positive when password was changed several times with different lifetime configuration
     *
     * @param int $userId
     * @return array
     */
    public function getLatestPassword($userId)
    {
        return $this->getConnection()->fetchRow(
            $this->getConnection()->select()->from(
                $this->getTable('admin_passwords')
            )->where(
                'user_id = :user_id'
            )->order(
                'password_id ' . \Magento\Framework\DB\Select::SQL_DESC
            )->limit(
                1
            ),
            [':user_id' => $userId]
        );
    }
}
