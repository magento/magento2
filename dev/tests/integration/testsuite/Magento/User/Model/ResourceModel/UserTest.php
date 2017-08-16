<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Model\ResourceModel;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;
use Magento\User\Model\ResourceModel\User as UserResourceModel;

/**
 * @magentoAppArea adminhtml
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    /** @var UserResourceModel */
    private $model;

    protected function setUp()
    {
        $this->model = Bootstrap::getObjectManager()->get(
            UserResourceModel::class
        );
    }

    /**
     * Tests if latest password is stored after user creating
     * when password lifetime config value is zero (disabled as fact)
     *
     * @magentoConfigFixture current_store admin/security/password_lifetime 0
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testGetLatestPasswordWhenZeroPasswordLifetime()
    {
        /** @var User $user */
        $user = Bootstrap::getObjectManager()->create(
            User::class
        );
        $user->loadByUsername('dummy_username');
        $latestPassword = $this->model->getLatestPassword($user->getId());

        $this->assertNotEmpty(
            $latestPassword,
            'Latest password should be stored even if password lifetime config value is 0'
        );
    }

    public function testCountAll()
    {
        $this->assertSame(1, $this->model->countAll());
    }

    public function testGetValidationRulesBeforeSave()
    {
        $rules = $this->model->getValidationRulesBeforeSave();
        $this->assertInstanceOf('Zend_Validate_Interface', $rules);
    }
}
