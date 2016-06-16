<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\Acl\Role\User;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Setup\Module\Setup;

class AdminAccount
{
    /**#@+
     * Data keys
     */
    const KEY_USER = 'admin-user';
    const KEY_PASSWORD = 'admin-password';
    const KEY_EMAIL = 'admin-email';
    const KEY_FIRST_NAME = 'admin-firstname';
    const KEY_LAST_NAME = 'admin-lastname';
    /**#@- */

    /**
     * Setup
     *
     * @var Setup
     */
    private $setup;

    /**
     * Configurations
     *
     * @var []
     */
    private $data;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Default Constructor
     *
     * @param Setup $setup
     * @param EncryptorInterface $encryptor
     * @param array $data
     */
    public function __construct(
        Setup $setup,
        EncryptorInterface $encryptor,
        array $data
    ) {
        $this->setup  = $setup;
        $this->encryptor = $encryptor;
        $this->data = $data;
    }

    /**
     * Generate password string
     *
     * @return string
     */
    protected function generatePassword()
    {
        return $this->encryptor->getHash($this->data[self::KEY_PASSWORD], true);
    }

    /**
     * Save administrator account and user role to DB.
     *
     * If the administrator account exists, update it.
     *
     * @return void
     */
    public function save()
    {
        $adminId = $this->saveAdminUser();
        $this->saveAdminUserRole($adminId);
    }

    /**
     * Uses the information in data[] to create the admin user.
     *
     * If the username already exists, it will update the record with information from data[]
     * and set the is_active flag.
     *
     * @return int The admin user id
     */
    private function saveAdminUser()
    {
        $adminData = [
            'firstname' => $this->data[self::KEY_FIRST_NAME],
            'lastname'  => $this->data[self::KEY_LAST_NAME],
            'password'  => $this->generatePassword(),
            'is_active' => 1,
        ];
        $result = $this->setup->getConnection()->fetchRow(
            'SELECT user_id, username, email FROM ' . $this->setup->getTable('admin_user') . ' ' .
            'WHERE username = :username OR email = :email',
            ['username' => $this->data[self::KEY_USER], 'email' => $this->data[self::KEY_EMAIL]]
        );

        if (!empty($result)) {
            // User exists, update
            $this->validateUserMatches($result['username'], $result['email']);
            $adminId = $result['user_id'];
            $adminData['modified'] = date('Y-m-d H:i:s');
            $this->setup->getConnection()->update(
                $this->setup->getTable('admin_user'),
                $adminData,
                $this->setup->getConnection()->quoteInto('username = ?', $this->data[self::KEY_USER])
            );
        } else {
            // User does not exist, create it
            $adminData['username'] = $this->data[self::KEY_USER];
            $adminData['email'] = $this->data[self::KEY_EMAIL];
            $adminData['extra'] = serialize(null);
            $this->setup->getConnection()->insert(
                $this->setup->getTable('admin_user'),
                $adminData
            );
            $adminId = $this->setup->getConnection()->lastInsertId();
        }
        return $adminId;
    }

    /**
     * Validates that the username and email both match the user.
     *
     * @param string $username Existing user's username
     * @param string $email Existing user's email
     * @return void
     * @throws \Exception If the username and email do not both match data provided to install
     */
    public function validateUserMatches($username, $email)
    {
        if ((strcasecmp($email, $this->data[self::KEY_EMAIL]) == 0) &&
            (strcasecmp($username, $this->data[self::KEY_USER]) != 0)) {
            // email matched but username did not
            throw new \Exception(
                'An existing user has the given email but different username. '
                . 'Username and email both need to match an existing user or both be new.'
            );
        }
        if ((strcasecmp($username, $this->data[self::KEY_USER]) == 0) &&
            (strcasecmp($email, $this->data[self::KEY_EMAIL]) != 0)) {
            // username matched but email did not
            throw new \Exception(
                'An existing user has the given username but different email. '
                . 'Username and email both need to match an existing user or both be new.'
            );
        }
    }

    /**
     * Creates the admin user role if one does not exist.
     *
     * Do nothing if a role already exists for this user
     *
     * @param int $adminId User id of administrator to set role for
     * @return void
     */
    private function saveAdminUserRole($adminId)
    {
        $result = $this->setup->getConnection()->fetchRow(
            'SELECT * FROM ' . $this->setup->getTable('authorization_role') . ' ' .
            'WHERE user_id = :user_id AND user_type = :user_type',
            ['user_id' => $adminId, 'user_type' => UserContextInterface::USER_TYPE_ADMIN]
        );
        if (empty($result)) {
            // No user role exists for this user id, create it
            $adminRoleData = [
                'parent_id'  => $this->retrieveAdministratorsRoleId(),
                'tree_level' => 2,
                'role_type'  => User::ROLE_TYPE,
                'user_id'    => $adminId,
                'user_type'  => UserContextInterface::USER_TYPE_ADMIN,
                'role_name'  => $this->data[self::KEY_USER],
            ];
            $this->setup->getConnection()->insert($this->setup->getTable('authorization_role'), $adminRoleData);
        }
    }

    /**
     * Gets the "Administrators" role id, the special role created by data fixture in Authorization module.
     *
     * @return int The id of the Administrators role
     * @throws \Exception If Administrators role not found or problem connecting with database.
     */
    private function retrieveAdministratorsRoleId()
    {
        // Get Administrators role id to use as parent_id
        $administratorsRoleData = [
            'parent_id'  => 0,
            'tree_level' => 1,
            'role_type' => Group::ROLE_TYPE,
            'user_id' => 0,
            'user_type' => UserContextInterface::USER_TYPE_ADMIN,
            'role_name' => 'Administrators',
        ];
        $result = $this->setup->getConnection()->fetchRow(
            'SELECT * FROM ' . $this->setup->getTable('authorization_role') . ' ' .
            'WHERE parent_id = :parent_id AND tree_level = :tree_level AND role_type = :role_type AND ' .
            'user_id = :user_id AND user_type = :user_type AND role_name = :role_name',
            $administratorsRoleData
        );
        if (empty($result)) {
            throw new \Exception('No Administrators role was found, data fixture needs to be run');
        } else {
            // Found at least one, use first
            return $result['role_id'];
        }
    }
}
