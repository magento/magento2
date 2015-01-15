<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Fixture\User;

use Mtf\ObjectManager;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Magento\User\Test\Fixture\AdminUserRole;

/**
 * Current password source.
 */
class CurrentPassword implements FixtureInterface
{
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
     * @param string $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = '')
    {
        $this->params = $params;
        /** @var \Mtf\System\Config $systemConfig */
        $systemConfig = ObjectManager::getInstance()->create('Mtf\System\Config');
        $superAdminPassword = $systemConfig->getConfigParam('application/backend_user_credentials/password');
        $this->data = $data == '%current_password%' ? $superAdminPassword : $data;
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
}
