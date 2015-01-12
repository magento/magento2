<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Fixture\AdminUserRole;

use Magento\User\Test\Fixture\User;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class InRoleUsers
 *
 * Data keys:
 *  - dataSet
 */
class InRoleUsers implements FixtureInterface
{
    /**
     * Array with data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * Array with Admin Users
     *
     * @var array
     */
    protected $adminUsers;

    /**
     * Array with usernames
     *
     * @var array
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
            $dataSets = explode(',', $data['dataSet']);
            foreach ($dataSets as $dataSet) {
                $adminUser = $fixtureFactory->createByCode('user', ['dataSet' => trim($dataSet)]);
                if (!$adminUser->hasData('user_id')) {
                    $adminUser->persist();
                }
                $this->adminUsers[] = $adminUser;
                $this->data[] = $adminUser->getUsername();
            }
        }
    }

    /**
     * Persist user role
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return array with usernames
     *
     * @param string $key [optional]
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
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
