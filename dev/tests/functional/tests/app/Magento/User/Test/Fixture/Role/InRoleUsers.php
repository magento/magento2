<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Fixture\Role;

use Magento\User\Test\Fixture\User;
use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Class InRoleUsers
 *
 * Data keys:
 *  - dataset
 */
class InRoleUsers extends DataSource
{
    /**
     * Array with Admin Users
     *
     * @var array
     */
    protected $adminUsers;

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
            $datasets = explode(',', $data['dataset']);
            foreach ($datasets as $dataset) {
                $adminUser = $fixtureFactory->createByCode('user', ['dataset' => trim($dataset)]);
                if (!$adminUser->hasData('user_id')) {
                    $adminUser->persist();
                }
                $this->adminUsers[] = $adminUser;
                $this->data[] = $adminUser->getUsername();
            }
        }
    }

    /**
     * Return array with admin user fixtures
     *
     * @return array
     */
    public function getAdminUsers()
    {
        return $this->adminUsers;
    }
}
