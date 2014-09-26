<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Setup\Model;

use Magento\Framework\Math\Random;
use Magento\Setup\Module\Setup;

class AdminAccount
{
    /**#@+
     * Data keys
     */
    const KEY_USERNAME = 'admin_username';
    const KEY_PASSWORD = 'admin_password';
    const KEY_EMAIL = 'admin_email';
    const KEY_FIRST_NAME = 'admin_firstname';
    const KEY_LAST_NAME = 'admin_lastname';
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
     * Random Generator
     *
     * @var \Magento\Framework\Math\Random
     */
    private $random;

    /**
     * Default Constructor
     *
     * @param Setup $setup
     * @param Random $random
     * @param array $data
     */
    public function __construct(Setup $setup, Random $random, array $data)
    {
        $this->setup  = $setup;
        $this->random = $random;
        $this->data = $data;
    }

    /**
     * Generate password string
     *
     * @return string
     */
    protected function generatePassword()
    {
        $salt = $this->random->getRandomString(32);
        return md5($salt . $this->data[self::KEY_PASSWORD]) . ':' . $salt;
    }

    /**
     * Save administrator account to DB
     *
     * @return void
     */
    public function save()
    {
        $adminData = [
            'firstname' => $this->data[self::KEY_FIRST_NAME],
            'lastname' => $this->data[self::KEY_LAST_NAME],
            'username' => $this->data[self::KEY_USERNAME],
            'password' => $this->generatePassword(),
            'email' => $this->data[self::KEY_EMAIL],
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
            'extra' => serialize(null),
            'is_active' => 1,
        ];
        $this->setup->getConnection()->insert(
            $this->setup->getTable('admin_user'),
            $adminData,
            true
        );
        $adminId = $this->setup->getConnection()->getDriver()->getLastGeneratedValue();

        $roles = [
            0 => [
                'parent_id' => 0,
                'tree_level' => 1,
                'sort_order' => 1,
                'role_type' => 'G',
                'user_id' => 0,
                'user_type' => 2,
                'role_name' => 'Administrators',
            ],
            1 => [
                'parent_id' => 1,
                'tree_level' => 2,
                'sort_order' => 0,
                'role_type' => 'U',
                'user_id' => $adminId,
                'user_type' => 2,
                'role_name' => $this->data[self::KEY_USERNAME],
            ]
        ];

        foreach ($roles as $role) {
            $this->setup->getConnection()->insert($this->setup->getTable('authorization_role'), $role, true);
        }
    }
}
