<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model;

use Magento\Backend\Model\Auth as AuthModel;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\DataFixture;
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
        $objectManager = Bootstrap::getObjectManager();
        $this->authModel = $objectManager->create(AuthModel::class);
        $this->userModel = $objectManager->create(UserModel::class);
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
        $user = DataFixtureStorageManager::getStorage()->get('user');
        $userName = $user->getDataByKey('username');
        $this->authModel->login(
            $userName,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $this->userModel->loadByUsername($userName);
        $this->assertNotNull($this->userModel->getLogdate());
    }
}
