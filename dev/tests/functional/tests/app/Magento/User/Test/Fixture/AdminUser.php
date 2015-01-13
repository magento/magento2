<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Fixture;

use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;
use Mtf\ObjectManager;
use Mtf\System\Config;

/**
 * Fixture with all necessary data for user creation on backend
 *
 */
class AdminUser extends DataFixture
{
    /**
     * @param Config $configuration
     * @param array $placeholders
     */
    public function __construct(Config $configuration, $placeholders = [])
    {
        $placeholders['password'] = isset($placeholders['password']) ? $placeholders['password'] : '123123q';
        parent::__construct($configuration, $placeholders);
        $this->_placeholders['sales_all_scopes'] = [$this, 'roleProvider'];
    }

    /**
     * Retrieve specify data from role.trieve specify data from role.
     *
     * @param $roleName
     * @return mixed
     */
    protected function roleProvider($roleName)
    {
        $role = Factory::getFixtureFactory()->getMagentoUserRole();
        $role->switchData($roleName);
        $data = $role->persist();
        return $data['id'];
    }

    /**
     * initialize data
     */
    protected function _initData()
    {
        /** @var \Mtf\System\Config $systemConfig */
        $systemConfig = ObjectManager::getInstance()->create('Mtf\System\Config');
        $superAdminPassword = $systemConfig->getConfigParam('application/backend_user_credentials/password');
        $this->_data = [
            'fields' => [
                'email' => [
                    'value' => 'email%isolation%@example.com',
                ],
                'firstname' => [
                    'value' => 'firstname%isolation%',
                ],
                'lastname' => [
                    'value' => 'lastname%isolation%',
                ],
                'password' => [
                    'value' => '%password%',
                ],
                'password_confirmation' => [
                    'value' => '%password%',
                ],
                'roles' => [
                    'value' => ['1'],
                ],
                'username' => [
                    'value' => 'admin%isolation%',
                ],
                'current_password' => [
                    'value' => $superAdminPassword,
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getData('fields/email/value');
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->getData('fields/password/value');
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getData('fields/username/value');
    }

    /**
     * Create user
     */
    public function persist()
    {
        Factory::getApp()->magentoUserCreateUser($this);
    }

    /**
     * Set password for user
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->_data['fields']['password']['value'] = $password;
        $this->_data['fields']['password_confirmation']['value'] = $password;
    }
}
