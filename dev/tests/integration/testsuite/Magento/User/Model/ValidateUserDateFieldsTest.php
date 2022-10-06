<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Model;

use Magento\Backend\Model\Auth as AuthModel;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User as UserModel;
use Magento\User\Test\Fixture\User as UserDataFixture;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class ValidateUserDateFieldsTest extends TestCase
{

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var AuthModel
     */
    protected $authModel;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        Bootstrap::getInstance()->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->authModel = $this->objectManager->create(AuthModel::class);
        $this->userModel = $this->objectManager->create(UserModel::class);
    }

    /**
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        DataFixture(UserDataFixture::class, ['role_id' => 1], 'user')
    ]
    public function testLogDate()
    {
        $user = $this->fixtures->get('user');
        $userName = $user->getDataByKey('username');
        $this->authModel->login(
            $userName,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $this->userModel->loadByUsername($userName);
        $this->assertNotNull($this->userModel->getLogdate());
    }
}
