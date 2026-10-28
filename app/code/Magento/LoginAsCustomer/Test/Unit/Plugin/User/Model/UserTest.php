<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Test\Unit\Plugin\User\Model;

use Exception;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\LoginAsCustomer\Model\Validator\UserRolePermission;
use Magento\LoginAsCustomer\Plugin\User\Model\User;
use Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForUserInterface;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\User as UserModel;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for LoginAsCustomer User plugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UserTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var User
     */
    private User $plugin;

    /**
     * @var Session
     */
    private Session $authSessionMock;

    /**
     * @var DeleteAuthenticationDataForUserInterface
     */
    private DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUserMock;

    /**
     * @var UserRolePermission
     */
    private UserRolePermission $validatorMock;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $loggerMock;

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->authSessionMock = $this->createMock(Session::class);
        $this->deleteAuthenticationDataForUserMock = $this->createMock(
            DeleteAuthenticationDataForUserInterface::class
        );
        $this->validatorMock = $this->createMock(UserRolePermission::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->plugin = $this->objectManager->getObject(
            User::class,
            [
                'authSession' => $this->authSessionMock,
                'deleteAuthenticationDataForUser' => $this->deleteAuthenticationDataForUserMock,
                'validator' => $this->validatorMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * Create a mock UserModel with proper expectations
     *
     * @param int|null $userId
     * @param array|null $roles
     * @param bool $skipValidation
     * @return MockObject
     */
    private function createUserModelMock($userId = null, $roles = null, $skipValidation = false): MockObject
    {
        $userModelMock = $this->createPartialMockWithReflection(
            UserModel::class,
            ['getId', 'getRoles', 'getSkipRoleResourceValidation']
        );

        if ($userId !== null) {
            $userModelMock->expects($this->any())
                ->method('getId')
                ->willReturn($userId);
        }

        if ($roles !== null) {
            $userModelMock->expects($this->any())
                ->method('getRoles')
                ->willReturn($roles);
        }

        $userModelMock->expects($this->any())
            ->method('getSkipRoleResourceValidation')
            ->willReturn($skipValidation);

        return $userModelMock;
    }

    /**
     * Test beforeSave method with existing user
     */
    public function testBeforeSaveWithExistingUser(): void
    {
        $userId = 123;
        $currentRoles = [5];
        $userModelMock = $this->createUserModelMock($userId, $currentRoles);

        $this->plugin->beforeSave($userModelMock);

        $this->addToAssertionCount(1);
    }

    /**
     * Test beforeSave method with new user (no ID)
     */
    public function testBeforeSaveWithNewUser(): void
    {
        $userModelMock = $this->createUserModelMock(null, null);

        $userModelMock->expects($this->never())
            ->method('getRoles');

        $this->plugin->beforeSave($userModelMock);

        $this->addToAssertionCount(1);
    }

    /**
     * Test beforeSave method with existing user but no roles
     */
    public function testBeforeSaveWithExistingUserNoRoles(): void
    {
        $userId = 123;
        $userModelMock = $this->createUserModelMock($userId, null);

        $this->plugin->beforeSave($userModelMock);

        $this->addToAssertionCount(1);
    }

    /**
     * Test afterSave method with role change that requires cleanup
     */
    public function testAfterSaveWithRoleChangeRequiringCleanup(): void
    {
        $userId = 123;
        $oldRoleId = 5;

        // First call beforeSave to store original role - use the SAME mock object
        $userModelMock = $this->createUserModelMock($userId, [$oldRoleId], false);
        $this->plugin->beforeSave($userModelMock);

        // Now test afterSave - use the same mock object for the result
        $userInterfaceMock = $this->createMock(UserInterface::class);

        $this->validatorMock->expects($this->once())
            ->method('validateUser')
            ->with($userModelMock, $oldRoleId)
            ->willReturn(true);

        $this->deleteAuthenticationDataForUserMock->expects($this->once())
            ->method('execute')
            ->with($userId);

        $this->loggerMock->expects($this->never())
            ->method('error');

        $result = $this->plugin->afterSave($userInterfaceMock, $userModelMock);

        $this->assertSame($userModelMock, $result);
    }

    /**
     * Test afterSave method with role change that doesn't require cleanup
     */
    public function testAfterSaveWithRoleChangeNotRequiringCleanup(): void
    {
        $userId = 123;
        $oldRoleId = 5;

        // First call beforeSave to store original role - use the SAME mock object
        $userModelMock = $this->createUserModelMock($userId, [$oldRoleId], false);
        $this->plugin->beforeSave($userModelMock);

        // Now test afterSave - use the same mock object for the result
        $userInterfaceMock = $this->createMock(UserInterface::class);

        $this->validatorMock->expects($this->once())
            ->method('validateUser')
            ->with($userModelMock, $oldRoleId)
            ->willReturn(false);

        $this->deleteAuthenticationDataForUserMock->expects($this->never())
            ->method('execute');

        $this->loggerMock->expects($this->never())
            ->method('error');

        $result = $this->plugin->afterSave($userInterfaceMock, $userModelMock);

        $this->assertSame($userModelMock, $result);
    }

    /**
     * Test afterSave method when skipRoleResourceValidation is true
     */
    public function testAfterSaveWithSkipValidation(): void
    {
        $userId = 123;
        $userInterfaceMock = $this->createMock(UserInterface::class);
        $resultUserMock = $this->createUserModelMock($userId, null, true);

        $this->validatorMock->expects($this->never())
            ->method('validateUser');

        $this->deleteAuthenticationDataForUserMock->expects($this->never())
            ->method('execute');

        $this->loggerMock->expects($this->never())
            ->method('error');

        $result = $this->plugin->afterSave($userInterfaceMock, $resultUserMock);

        $this->assertSame($resultUserMock, $result);
    }

    /**
     * Test afterSave method with exception handling
     */
    public function testAfterSaveWithException(): void
    {
        $userId = 123;
        $oldRoleId = 5;
        $exceptionMessage = 'Test exception';

        // First call beforeSave to store original role - use the SAME mock object
        $userModelMock = $this->createUserModelMock($userId, [$oldRoleId], false);
        $this->plugin->beforeSave($userModelMock);

        // Now test afterSave with exception - use the same mock object
        $userInterfaceMock = $this->createMock(UserInterface::class);

        $this->validatorMock->expects($this->once())
            ->method('validateUser')
            ->with($userModelMock, $oldRoleId)
            ->willThrowException(new Exception($exceptionMessage));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exceptionMessage);

        $this->deleteAuthenticationDataForUserMock->expects($this->never())
            ->method('execute');

        $result = $this->plugin->afterSave($userInterfaceMock, $userModelMock);

        $this->assertSame($userModelMock, $result);
    }

    /**
     * Test afterSave method with LocalizedException handling
     */
    public function testAfterSaveWithLocalizedException(): void
    {
        $userId = 123;
        $oldRoleId = 5;
        $exceptionMessage = 'Localized exception';

        // First call beforeSave to store original role - use the SAME mock object
        $userModelMock = $this->createUserModelMock($userId, [$oldRoleId], false);
        $this->plugin->beforeSave($userModelMock);

        // Now test afterSave with LocalizedException - use the same mock object
        $userInterfaceMock = $this->createMock(UserInterface::class);

        $this->validatorMock->expects($this->once())
            ->method('validateUser')
            ->with($userModelMock, $oldRoleId)
            ->willThrowException(new LocalizedException(__($exceptionMessage)));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains($exceptionMessage));

        $this->deleteAuthenticationDataForUserMock->expects($this->never())
            ->method('execute');

        $result = $this->plugin->afterSave($userInterfaceMock, $userModelMock);

        $this->assertSame($userModelMock, $result);
    }

    /**
     * Test afterSave method with user not found in original roles
     */
    public function testAfterSaveWithUserNotInOriginalRoles(): void
    {
        $userId = 123;
        $userInterfaceMock = $this->createMock(UserInterface::class);
        $resultUserMock = $this->createUserModelMock($userId, null, false);

        $this->validatorMock->expects($this->once())
            ->method('validateUser')
            ->with($resultUserMock, null) // Should pass null for oldRoleId
            ->willReturn(false);

        $this->deleteAuthenticationDataForUserMock->expects($this->never())
            ->method('execute');

        $this->loggerMock->expects($this->never())
            ->method('error');

        $result = $this->plugin->afterSave($userInterfaceMock, $resultUserMock);

        $this->assertSame($resultUserMock, $result);
    }

    /**
     * Test afterSave method when exception occurs during execute
     */
    public function testAfterSaveWithExceptionDuringExecute(): void
    {
        $userId = 123;
        $oldRoleId = 5;
        $exceptionMessage = 'Execute exception';

        // First call beforeSave to store original role - use the SAME mock object
        $userModelMock = $this->createUserModelMock($userId, [$oldRoleId], false);
        $this->plugin->beforeSave($userModelMock);

        // Now test afterSave with exception during execute - use the same mock object
        $userInterfaceMock = $this->createMock(UserInterface::class);

        $this->validatorMock->expects($this->once())
            ->method('validateUser')
            ->with($userModelMock, $oldRoleId)
            ->willReturn(true);

        $this->deleteAuthenticationDataForUserMock->expects($this->once())
            ->method('execute')
            ->with($userId)
            ->willThrowException(new Exception($exceptionMessage));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exceptionMessage);

        $result = $this->plugin->afterSave($userInterfaceMock, $userModelMock);

        $this->assertSame($userModelMock, $result);
    }
}
