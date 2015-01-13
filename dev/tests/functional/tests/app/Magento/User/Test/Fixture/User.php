<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Fixture;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\InjectableFixture;
use Mtf\Handler\HandlerFactory;
use Mtf\Repository\RepositoryFactory;
use Mtf\System\Config;
use Mtf\System\Event\EventManagerInterface;

/**
 * Class User
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class User extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\User\Test\Repository\User';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\User\Test\Handler\User\UserInterface';

    protected $defaultDataSet = [
        'username' => 'AdminUser%isolation%',
        'firstname' => 'FirstName%isolation%',
        'lastname' => 'LastName%isolation%',
        'email' => 'email%isolation%@example.com',
        'password' => '123123q',
        'password_confirmation' => '123123q',
        'is_active' => 'Active',
    ];

    protected $user_id = [
        'attribute_code' => 'user_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $firstname = [
        'attribute_code' => 'firstname',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'user-info',
    ];

    protected $lastname = [
        'attribute_code' => 'lastname',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'user-info',
    ];

    protected $email = [
        'attribute_code' => 'email',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'user-info',
    ];

    protected $username = [
        'attribute_code' => 'username',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'user-info',
    ];

    protected $password = [
        'attribute_code' => 'password',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'user-info',
    ];

    protected $created = [
        'attribute_code' => 'created',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => 'CURRENT_TIMESTAMP',
        'input' => '',
    ];

    protected $modified = [
        'attribute_code' => 'modified',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $logdate = [
        'attribute_code' => 'logdate',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $lognum = [
        'attribute_code' => 'lognum',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $reload_acl_flag = [
        'attribute_code' => 'reload_acl_flag',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $is_active = [
        'attribute_code' => 'is_active',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '1',
        'input' => '',
    ];

    protected $extra = [
        'attribute_code' => 'extra',
        'backend_type' => 'text',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $rp_token = [
        'attribute_code' => 'rp_token',
        'backend_type' => 'text',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $rp_token_created_at = [
        'attribute_code' => 'rp_token_created_at',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $interface_locale = [
        'attribute_code' => 'interface_locale',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => 'en_US',
        'input' => '',
    ];

    protected $role_id = [
        'attribute_code' => 'role_id',
        'backend_type' => 'virtual',
        'group' => 'user-role',
        'source' => 'Magento\User\Test\Fixture\User\RoleId',
    ];

    protected $password_confirmation = [
        'attribute_code' => 'password_confirmation',
        'backend_type' => 'virtual',
        'group' => 'user-info',
    ];

    protected $current_password = [
        'attribute_code' => 'current_password',
        'backend_type' => 'virtual',
        'group' => 'user-info',
    ];

    /**
     * Initialize dependencies.
     *
     * @param Config $configuration
     * @param RepositoryFactory $repositoryFactory
     * @param FixtureFactory $fixtureFactory
     * @param HandlerFactory $handlerFactory
     * @param EventManagerInterface $eventManager
     * @param array $data
     * @param string $dataSet
     * @param bool $persist
     */
    public function __construct(
        Config $configuration,
        RepositoryFactory $repositoryFactory,
        FixtureFactory $fixtureFactory,
        HandlerFactory $handlerFactory,
        EventManagerInterface $eventManager,
        array $data = [],
        $dataSet = '',
        $persist = false
    ) {
        $this->defaultDataSet['current_password'] = $configuration
            ->getConfigParam('application/backend_user_credentials/password');
        parent::__construct(
            $configuration,
            $repositoryFactory,
            $fixtureFactory,
            $handlerFactory,
            $eventManager,
            $data,
            $dataSet,
            $persist
        );
    }

    public function getUserId()
    {
        return $this->getData('user_id');
    }

    public function getFirstname()
    {
        return $this->getData('firstname');
    }

    public function getLastname()
    {
        return $this->getData('lastname');
    }

    public function getEmail()
    {
        return $this->getData('email');
    }

    public function getUsername()
    {
        return $this->getData('username');
    }

    public function getPassword()
    {
        return $this->getData('password');
    }

    public function getCreated()
    {
        return $this->getData('created');
    }

    public function getModified()
    {
        return $this->getData('modified');
    }

    public function getLogdate()
    {
        return $this->getData('logdate');
    }

    public function getLognum()
    {
        return $this->getData('lognum');
    }

    public function getReloadAclFlag()
    {
        return $this->getData('reload_acl_flag');
    }

    public function getIsActive()
    {
        return $this->getData('is_active');
    }

    public function getExtra()
    {
        return $this->getData('extra');
    }

    public function getRpToken()
    {
        return $this->getData('rp_token');
    }

    public function getRpTokenCreatedAt()
    {
        return $this->getData('rp_token_created_at');
    }

    public function getInterfaceLocale()
    {
        return $this->getData('interface_locale');
    }

    public function getRoleId()
    {
        return $this->getData('role_id');
    }

    public function getPasswordConfirmation()
    {
        return $this->getData('password_confirmation');
    }

    public function getCurrentPassword()
    {
        return $this->getData('current_password');
    }
}
