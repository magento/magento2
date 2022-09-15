<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\AdminAdobeIms\Model\User;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\AdminAdobeIms\Logger\AdminAdobeImsLogger;
use Magento\Authorization\Test\Fixture\Role as RoleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\Framework\App\Area;
use Magento\User\Model\User as AdminUser;
use Magento\Authorization\Model\Role;
use Magento\Backend\Model\Auth\Session;
use Magento\AdminAdobeIms\Model\SaveImsUser;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Test class for adding Admin IMS User with default "Adobe Ims" Role
 *
 * @magentoDbIsolation disabled
 */
class SaveImsUserTest extends TestCase
{
    private const ADMIN_IMS_ROLE = 'Adobe Ims';

    /**
     * @var Bootstrap
     */
    private $objectManager;

    /**
     * @var User
     */
    private $user;

    /**
     * @var UserCollectionFactory
     */
    private $userCollectionFactory;

    /**
     * @var RoleCollectionFactory
     */
    private $roleCollectionFactory;

    /**
     * @var AdminAdobeImsLogger
     */
    private $logger;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var SaveImsUser
     */
    private $saveImsUser;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager            = Bootstrap::getObjectManager();
        $this->user                     = $this->objectManager->create(User::class);
        $this->userCollectionFactory    = $this->objectManager->create(UserCollectionFactory::class);
        $this->roleCollectionFactory    = $this->objectManager->create(RoleCollectionFactory::class);
        $this->logger                   = $this->createMock(AdminAdobeImsLogger::class);
        $this->authSession              = $this->objectManager->create(Session::class);
        $this->saveImsUser              = $this->objectManager->create(
            SaveImsUser::class,
            [
                'user'                  => $this->user,
                'userCollectionFactory' => $this->userCollectionFactory,
                'roleCollectionFactory' => $this->roleCollectionFactory,
                'logger'                => $this->logger
            ]
        );
    }

    /**
     * Import Adobe Ims User into Adobe Commerce
     *
     * @magentoDbIsolation disabled
     * @return void
     */
    #[
        AppArea(Area::AREA_ADMINHTML),
        DataFixture(RoleFixture::class, ['role_name' => self::ADMIN_IMS_ROLE]),
    ]
    public function testImportImsUserToAdobeCommerce(): void
    {
        $profile = [
            'emailVerified'         => 'true',
            'account_type'          => 'type2e',
            'preferred_languages'   => null,
            'displayName'           => 'ImsFirstname1 ImsLastname1',
            'name'                  => 'ImsFirstname1 ImsLastname1',
            'last_name'             => 'ImsLastname1',
            'userId'                => '100001',
            'first_name'            => 'ImsFirstname1',
            'email'                 => 'imsuser1@admin.com',
        ];

        $this->saveImsUser->save($profile);

        $savedUserId = $this->user->getUserId();
        //Check whether Adobe Ims User is saved
        $this->assertEquals($profile['email'], $this->user->load($savedUserId)->getEmail());
        $this->assertEquals($profile['first_name'], $this->user->load($savedUserId)->getFirstname());
        //Delete Assigned Role for Adobe Ims User
        /** @var Role $roleModel */
        $roleModel = $this->objectManager->create(Role::class);
        $roleModel->load($savedUserId, 'user_id');
        $roleModel->delete();
        //Delete Adobe Ims Admin User
        /** @var AdminUser $userModel */
        $userModel = $this->objectManager->create(AdminUser::class);
        $userModel->load($savedUserId);
        $userModel->delete();
    }

    /**
     * Handle Exception while Importing Adobe Ims User into Adobe Commerce
     *
     * @return void
     * @throws CouldNotSaveException
     */
    #[
        AppArea(Area::AREA_ADMINHTML),
        DataFixture(RoleFixture::class, ['role_name' => self::ADMIN_IMS_ROLE]),
    ]
    public function testExceptionWhenSaveImsUserFails(): void
    {
        $profile = [
            'email' => 'imsuser2@admin.com',
        ];
        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save ims user.');

        $this->saveImsUser->save($profile);
    }
}
