<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Fixture\User;

use Magento\User\Test\Fixture\Role;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Source for Role of User.
 *
 * Data keys:
 *  - dataSet
 *  - role
 */
class RoleId implements FixtureInterface
{
    /**
     * Admin User Role.
     *
     * @var Role
     */
    protected $role;

    /**
     * User role name.
     *
     * @var string
     */
    protected $data;

    /**
     * @construct
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['dataSet']) && $data['dataSet'] !== '-') {
            list($fixtureCode, $dataSet) = explode('::', $data['dataSet']);
            $this->role = $fixtureFactory->createByCode($fixtureCode, ['dataSet' => $dataSet]);
            if (!$this->role->hasData('role_id')) {
                $this->role->persist();
            }
            $this->data = $this->role->getRoleName();
        }
        if (isset($data['role']) && $data['role'] instanceof Role) {
            $this->role = $data['role'];
            $this->data = $data['role']->getRoleName();
        } elseif (isset($data['value'])) {
            $this->data = $data['value'];
        }
    }

    /**
     * Persist user role.
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set.
     *
     * @param string $key [optional]
     * @return string|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings.
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Return role fixture.
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }
}
