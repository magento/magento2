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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\User\Test\Fixture;

use Mtf\Fixture\DataFixture;
use Mtf\Factory\Factory;
use Mtf\System\Config;
use Mtf\ObjectManager;

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
    public function __construct(Config $configuration, $placeholders = array())
    {
        $placeholders['password'] = isset($placeholders['password']) ? $placeholders['password'] : '123123q';
        parent::__construct($configuration, $placeholders);
        $this->_placeholders['sales_all_scopes'] = array($this, 'roleProvider');
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
        $this->_data = array(
            'fields' => array(
                'email' => array(
                    'value' => 'email%isolation%@example.com'
                ),
                'firstname' => array(
                    'value' => 'firstname%isolation%'
                ),
                'lastname' => array(
                    'value' => 'lastname%isolation%'
                ),
                'password' => array(
                    'value' => '%password%'
                ),
                'password_confirmation' => array(
                    'value' => '%password%'
                ),
                'roles' => array(
                    'value' => array('1')
                ),
                'username' => array(
                    'value' => 'admin%isolation%'
                ),
                'current_password' => array(
                    'value' => $superAdminPassword
                ),
            ),
        );
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
