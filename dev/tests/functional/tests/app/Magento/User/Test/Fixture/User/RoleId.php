<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Fixture\User;

use Magento\Mtf\Fixture\DataSource;
use Magento\User\Test\Fixture\Role;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Source for Role of User.
 *
 * Data keys:
 *  - dataset
 *  - role
 */
class RoleId extends DataSource
{
    /**
     * Admin User Role.
     *
     * @var Role
     */
    protected $role;

    /**
     * @construct
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['dataset']) && $data['dataset'] !== '-') {
            list($fixtureCode, $dataset) = explode('::', $data['dataset']);
            $this->role = $fixtureFactory->createByCode($fixtureCode, ['dataset' => $dataset]);
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
     * Return role fixture.
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }
}
