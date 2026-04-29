<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Test\Unit\Plugin\Authorization\Model\ResourceModel;

use Exception;
use Magento\Authorization\Model\Rules;
use Magento\Authorization\Model\ResourceModel\Rules as Subject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\LoginAsCustomer\Model\Validator\UserRolePermission;
use Magento\LoginAsCustomer\Plugin\Authorization\Model\ResourceModel\RulesPlugin;
use Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForListOfUserInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for LoginAsCustomer Authorization Rules plugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RulesPluginTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var RulesPlugin
     */
    private RulesPlugin $plugin;

    /**
     * @var DeleteAuthenticationDataForListOfUserInterface
     */
    private DeleteAuthenticationDataForListOfUserInterface $deleteAuthenticationDataForListOfUserMock;

    /**
     * @var UserRolePermission
     */
    private UserRolePermission $validatorMock;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $loggerMock;

    /**
     * @var Subject
     */
    private Subject $subjectMock;

    /**
     * @var Rules
     */
    private Rules $ruleMock;

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

        $this->deleteAuthenticationDataForListOfUserMock = $this->createMock(
            DeleteAuthenticationDataForListOfUserInterface::class
        );

        $this->validatorMock = $this->createMock(UserRolePermission::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->subjectMock = $this->createMock(Subject::class);

        $this->ruleMock = $this->createPartialMockWithReflection(
            Rules::class,
            ['getRoleUnassignedUsers']
        );

        $this->plugin = $this->objectManager->getObject(
            RulesPlugin::class,
            [
                'deleteAuthenticationDataForListOfUser' => $this->deleteAuthenticationDataForListOfUserMock,
                'validator' => $this->validatorMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * Test afterSaveRel method when users need to be terminated
     */
    public function testAfterSaveRelWithUsersToTerminate(): void
    {
        $result = null;
        $validatorUsers = [1, 2, 3];
        $unassignedUsers = [4, 5];
        $expectedUsers = [1, 2, 3, 4, 5];

        $this->validatorMock->expects($this->once())
            ->method('validateRoles')
            ->with($this->ruleMock)
            ->willReturn($validatorUsers);

        $this->ruleMock->expects($this->once())
            ->method('getRoleUnassignedUsers')
            ->willReturn($unassignedUsers);

        $this->deleteAuthenticationDataForListOfUserMock->expects($this->once())
            ->method('execute')
            ->with($expectedUsers);

        $this->loggerMock->expects($this->never())
            ->method('error');

        $this->plugin->afterSaveRel($this->subjectMock, $result, $this->ruleMock);
    }

    /**
     * Test afterSaveRel method when only validator returns users
     */
    public function testAfterSaveRelWithOnlyValidatorUsers(): void
    {
        $result = null;
        $validatorUsers = [1, 2, 3];
        $unassignedUsers = [];
        $expectedUsers = [1, 2, 3];

        $this->validatorMock->expects($this->once())
            ->method('validateRoles')
            ->with($this->ruleMock)
            ->willReturn($validatorUsers);

        $this->ruleMock->expects($this->once())
            ->method('getRoleUnassignedUsers')
            ->willReturn($unassignedUsers);

        $this->deleteAuthenticationDataForListOfUserMock->expects($this->once())
            ->method('execute')
            ->with($expectedUsers);

        $this->loggerMock->expects($this->never())
            ->method('error');

        $this->plugin->afterSaveRel($this->subjectMock, $result, $this->ruleMock);
    }

    /**
     * Test afterSaveRel method when only unassigned users exist
     */
    public function testAfterSaveRelWithOnlyUnassignedUsers(): void
    {
        $result = null;
        $validatorUsers = [];
        $unassignedUsers = [4, 5];
        $expectedUsers = [4, 5];

        $this->validatorMock->expects($this->once())
            ->method('validateRoles')
            ->with($this->ruleMock)
            ->willReturn($validatorUsers);

        $this->ruleMock->expects($this->once())
            ->method('getRoleUnassignedUsers')
            ->willReturn($unassignedUsers);

        $this->deleteAuthenticationDataForListOfUserMock->expects($this->once())
            ->method('execute')
            ->with($expectedUsers);

        $this->loggerMock->expects($this->never())
            ->method('error');

        $this->plugin->afterSaveRel($this->subjectMock, $result, $this->ruleMock);
    }

    /**
     * Test afterSaveRel method when no users need termination
     */
    public function testAfterSaveRelWithNoUsersToTerminate(): void
    {
        $result = null;
        $validatorUsers = [];
        $unassignedUsers = [];

        $this->validatorMock->expects($this->once())
            ->method('validateRoles')
            ->with($this->ruleMock)
            ->willReturn($validatorUsers);

        $this->ruleMock->expects($this->once())
            ->method('getRoleUnassignedUsers')
            ->willReturn($unassignedUsers);

        $this->deleteAuthenticationDataForListOfUserMock->expects($this->never())
            ->method('execute');

        $this->loggerMock->expects($this->never())
            ->method('error');

        $this->plugin->afterSaveRel($this->subjectMock, $result, $this->ruleMock);
    }

    /**
     * Test afterSaveRel method when duplicate users are present
     */
    public function testAfterSaveRelWithDuplicateUsers(): void
    {
        $result = null;
        $validatorUsers = [1, 2, 3];
        $unassignedUsers = [2, 3, 4]; // Contains duplicates with validator users
        // Simulate exactly what the plugin does: array_unique(array_merge(...))
        $merged = array_merge($validatorUsers, $unassignedUsers);
        $expectedUsers = array_unique($merged); // This preserves keys: [0=>1, 1=>2, 2=>3, 5=>4]

        $this->validatorMock->expects($this->once())
            ->method('validateRoles')
            ->with($this->ruleMock)
            ->willReturn($validatorUsers);

        $this->ruleMock->expects($this->once())
            ->method('getRoleUnassignedUsers')
            ->willReturn($unassignedUsers);

        $this->deleteAuthenticationDataForListOfUserMock->expects($this->once())
            ->method('execute')
            ->with($expectedUsers);

        $this->loggerMock->expects($this->never())
            ->method('error');

        $this->plugin->afterSaveRel($this->subjectMock, $result, $this->ruleMock);
    }

    /**
     * Test afterSaveRel method when exception is thrown during validation
     */
    public function testAfterSaveRelWithExceptionDuringValidation(): void
    {
        $result = null;
        $exceptionMessage = 'Validation exception';
        $exception = new Exception($exceptionMessage);

        $this->validatorMock->expects($this->once())
            ->method('validateRoles')
            ->with($this->ruleMock)
            ->willThrowException($exception);

        $this->ruleMock->expects($this->never())
            ->method('getRoleUnassignedUsers');

        $this->deleteAuthenticationDataForListOfUserMock->expects($this->never())
            ->method('execute');

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exceptionMessage);

        $this->plugin->afterSaveRel($this->subjectMock, $result, $this->ruleMock);
    }

    /**
     * Test afterSaveRel method when exception is thrown during getRoleUnassignedUsers
     */
    public function testAfterSaveRelWithExceptionDuringGetUnassignedUsers(): void
    {
        $result = null;
        $validatorUsers = [1, 2, 3];
        $exceptionMessage = 'Unassigned users exception';
        $exception = new Exception($exceptionMessage);

        $this->validatorMock->expects($this->once())
            ->method('validateRoles')
            ->with($this->ruleMock)
            ->willReturn($validatorUsers);

        $this->ruleMock->expects($this->once())
            ->method('getRoleUnassignedUsers')
            ->willThrowException($exception);

        $this->deleteAuthenticationDataForListOfUserMock->expects($this->never())
            ->method('execute');

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exceptionMessage);

        $this->plugin->afterSaveRel($this->subjectMock, $result, $this->ruleMock);
    }

    /**
     * Test afterSaveRel method when exception is thrown during execute
     */
    public function testAfterSaveRelWithExceptionDuringExecute(): void
    {
        $result = null;
        $validatorUsers = [1, 2, 3];
        $unassignedUsers = [4, 5];
        $expectedUsers = [1, 2, 3, 4, 5];
        $exceptionMessage = 'Execute exception';
        $exception = new Exception($exceptionMessage);

        $this->validatorMock->expects($this->once())
            ->method('validateRoles')
            ->with($this->ruleMock)
            ->willReturn($validatorUsers);

        $this->ruleMock->expects($this->once())
            ->method('getRoleUnassignedUsers')
            ->willReturn($unassignedUsers);

        $this->deleteAuthenticationDataForListOfUserMock->expects($this->once())
            ->method('execute')
            ->with($expectedUsers)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exceptionMessage);

        $this->plugin->afterSaveRel($this->subjectMock, $result, $this->ruleMock);
    }

    /**
     * Test afterSaveRel method with different result types
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('resultTypesDataProvider')]
    public function testAfterSaveRelWithDifferentResultTypes(mixed $result): void
    {
        $validatorUsers = [];
        $unassignedUsers = [];

        $this->validatorMock->expects($this->once())
            ->method('validateRoles')
            ->with($this->ruleMock)
            ->willReturn($validatorUsers);

        $this->ruleMock->expects($this->once())
            ->method('getRoleUnassignedUsers')
            ->willReturn($unassignedUsers);

        $this->deleteAuthenticationDataForListOfUserMock->expects($this->never())
            ->method('execute');

        $this->loggerMock->expects($this->never())
            ->method('error');

        $this->plugin->afterSaveRel($this->subjectMock, $result, $this->ruleMock);
    }

    /**
     * Data provider for different result types
     */
    public static function resultTypesDataProvider(): array
    {
        return [
            'null' => [null],
            'empty_string' => [''],
            'string' => ['string_result'],
            'integer' => [123],
            'array' => [['array_result']],
            'object' => [new \stdClass()],
        ];
    }

    /**
     * Test afterSaveRel method with various user ID types
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('userIdTypesDataProvider')]
    public function testAfterSaveRelWithDifferentUserIdTypes(array $validatorUsers, array $unassignedUsers): void
    {
        $result = null;
        // Simulate exactly what the plugin does
        $merged = array_merge($validatorUsers, $unassignedUsers);
        $expectedUsers = array_unique($merged);

        $this->validatorMock->expects($this->once())
            ->method('validateRoles')
            ->with($this->ruleMock)
            ->willReturn($validatorUsers);

        $this->ruleMock->expects($this->once())
            ->method('getRoleUnassignedUsers')
            ->willReturn($unassignedUsers);

        if ($expectedUsers) {
            $this->deleteAuthenticationDataForListOfUserMock->expects($this->once())
                ->method('execute')
                ->with($expectedUsers);
        } else {
            $this->deleteAuthenticationDataForListOfUserMock->expects($this->never())
                ->method('execute');
        }

        $this->loggerMock->expects($this->never())
            ->method('error');

        $this->plugin->afterSaveRel($this->subjectMock, $result, $this->ruleMock);
    }

    /**
     * Data provider for different user ID types
     */
    public static function userIdTypesDataProvider(): array
    {
        return [
            'integer_ids' => [[1, 2, 3], [4, 5]],
            'string_ids' => [['1', '2', '3'], ['4', '5']],
            'mixed_types' => [[1, '2', 3], ['4', 5]],
            'empty_arrays' => [[], []],
            'one_empty' => [[1, 2], []],
            'other_empty' => [[], [3, 4]],
        ];
    }

    /**
     * Test afterSaveRel method when getRoleUnassignedUsers returns false (original TypeError scenario)
     */
    public function testAfterSaveRelWithGetUnassignedUsersReturnsFalse(): void
    {
        $result = null;
        $validatorUsers = [1, 2, 3];
        $expectedUsers = [1, 2, 3];

        $this->validatorMock->expects($this->once())
            ->method('validateRoles')
            ->with($this->ruleMock)
            ->willReturn($validatorUsers);

        $this->ruleMock->expects($this->once())
            ->method('getRoleUnassignedUsers')
            ->willReturn(false); // This would cause TypeError in original code

        $this->deleteAuthenticationDataForListOfUserMock->expects($this->once())
            ->method('execute')
            ->with($expectedUsers);

        $this->loggerMock->expects($this->never())
            ->method('error');

        $this->plugin->afterSaveRel($this->subjectMock, $result, $this->ruleMock);
    }

    /**
     * Test afterSaveRel method when getRoleUnassignedUsers returns null (original TypeError scenario)
     */
    public function testAfterSaveRelWithGetUnassignedUsersReturnsNull(): void
    {
        $result = null;
        $validatorUsers = [1, 2, 3];
        $expectedUsers = [1, 2, 3];

        $this->validatorMock->expects($this->once())
            ->method('validateRoles')
            ->with($this->ruleMock)
            ->willReturn($validatorUsers);

        $this->ruleMock->expects($this->once())
            ->method('getRoleUnassignedUsers')
            ->willReturn(null);

        $this->deleteAuthenticationDataForListOfUserMock->expects($this->once())
            ->method('execute')
            ->with($expectedUsers);

        $this->loggerMock->expects($this->never())
            ->method('error');

        $this->plugin->afterSaveRel($this->subjectMock, $result, $this->ruleMock);
    }

    /**
     * Test afterSaveRel method when getRoleUnassignedUsers returns string (original TypeError scenario)
     */
    public function testAfterSaveRelWithGetUnassignedUsersReturnsString(): void
    {
        $result = null;
        $validatorUsers = [1, 2, 3];
        $expectedUsers = [1, 2, 3];

        $this->validatorMock->expects($this->once())
            ->method('validateRoles')
            ->with($this->ruleMock)
            ->willReturn($validatorUsers);

        $this->ruleMock->expects($this->once())
            ->method('getRoleUnassignedUsers')
            ->willReturn('not_an_array');

        $this->deleteAuthenticationDataForListOfUserMock->expects($this->once())
            ->method('execute')
            ->with($expectedUsers);

        $this->loggerMock->expects($this->never())
            ->method('error');

        $this->plugin->afterSaveRel($this->subjectMock, $result, $this->ruleMock);
    }
}
