<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Model\ResourceModel;

use Laminas\Validator\ValidatorInterface;
use Magento\Authorization\Model\ResourceModel\Role\Collection as UserRoleCollection;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as UserRoleCollectionFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Math\Random;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\ResourceModel\User as UserResourceModel;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;

/**
 * @magentoAppArea adminhtml
 */
class UserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UserResourceModel
     */
    private $model;

    /**
     * @var UserRoleCollectionFactory
     */
    private $userRoleCollectionFactory;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var Random
     */
    private $random;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->get(UserResourceModel::class);
        $this->userRoleCollectionFactory = Bootstrap::getObjectManager()->get(UserRoleCollectionFactory::class);
        $this->userFactory = Bootstrap::getObjectManager()->get(UserFactory::class);
        $this->random = Bootstrap::getObjectManager()->get(Random::class);
    }

    /**
     * Tests if latest password is stored after user creating
     * when password lifetime config value is zero (disabled as fact)
     *
     * @return void
     * @magentoConfigFixture current_store admin/security/password_lifetime 0
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testGetLatestPasswordWhenZeroPasswordLifetime(): void
    {
        /** @var User $user */
        $user = $this->userFactory->create();
        $user->loadByUsername('dummy_username');
        $latestPassword = $this->model->getLatestPassword($user->getId());

        $this->assertNotEmpty(
            $latestPassword,
            'Latest password should be stored even if password lifetime config value is 0'
        );
    }

    /**
     * Test that user role is not deleted after deleting empty user.
     *
     * @return void
     */
    public function testDelete(): void
    {
        $this->checkRoleCollectionSize();
        /** @var User $user */
        $user = $this->userFactory->create();
        $this->model->delete($user);
        $this->checkRoleCollectionSize();
    }

    /**
     * Ensure that role collection size is correct.
     *
     * @return void
     */
    private function checkRoleCollectionSize(): void
    {
        /** @var UserRoleCollection $roleCollection */
        $roleCollection = $this->userRoleCollectionFactory->create();
        $roleCollection->setUserFilter(0, UserContextInterface::USER_TYPE_ADMIN);
        $this->assertEquals(1, $roleCollection->getSize());
    }

    /**
     * Check total user count.
     *
     * @return void
     */
    public function testCountAll(): void
    {
        $this->assertSame(1, $this->model->countAll());
    }

    /**
     * Check validation rules has correct type.
     *
     * @return void
     */
    public function testGetValidationRulesBeforeSave(): void
    {
        $rules = $this->model->getValidationRulesBeforeSave();
        $this->assertInstanceOf(ValidatorInterface::class, $rules);
    }

    /**
     * Test save rp token
     *
     * @throws \Exception
     */
    public function testSave(): void
    {
        $token = 'randomstring';
        $username = $this->random->getRandomString(6);
        $email = $username . "@example.com";
        $password = uniqid() . $this->random->getRandomString(10);
        $userModel = Bootstrap::getObjectManager()->get(User::class);

        $userModel->setData(
            [
                'email' => $email,
                'rp_token' => $token,
                'firstname' => 'John',
                'lastname' => 'Doe',
                'password' => $password,
                'username' => $username
            ]
        )->save();

        $userResourceModel = $this->model;

        $this->assertEquals($token, $userModel->getRpToken());
        $this->assertNotEquals(
            $userModel->getRpToken(),
            $userResourceModel->load($userModel, 'rp_token')
        );
    }
}
