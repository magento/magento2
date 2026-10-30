<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\Test\Integration\Console\Command;

use InvalidArgumentException;
use Magento\Authorization\Model\Acl\Role\User as RoleUser;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Authorization\Test\Fixture\Role as RoleFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Console\Cli;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Console\AssignAdminToRoleCommand;
use Magento\User\Model\AssignUserToRole;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use Magento\User\Test\Fixture\User as UserFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AppArea('adminhtml')]
class AssignAdminToRoleCommandTest extends TestCase
{
    private const USER_NAME_OPTION = '--username';

    private const ROLE_ID_OPTION = '--role-id';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $command = $this->objectManager->create(AssignAdminToRoleCommand::class);
        $command->setHelperSet(new HelperSet([new QuestionHelper()]));
        $this->commandTester = new CommandTester($command);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        DataFixture(UserFixture::class, as: 'user'),
        DataFixture(RoleFixture::class, as: 'role'),
    ]
    public function testAssignsUserWithoutRoleToRole(): void
    {
        $user = $this->fixtures->get('user');
        $roleId = (int)$this->fixtures->get('role')->getId();

        $status = $this->commandTester->execute(
            [self::USER_NAME_OPTION => $user->getUserName(), self::ROLE_ID_OPTION => (string)$roleId],
            ['interactive' => false]
        );

        $this->assertSame(Cli::RETURN_SUCCESS, $status);
        $this->assertSame([$roleId], $this->getAssignedRoleIds($user->getUserName()));
    }

    #[
        DataFixture(RoleFixture::class, as: 'role1'),
        DataFixture(RoleFixture::class, as: 'role2'),
        DataFixture(UserFixture::class, ['role_id' => '$role1.id$'], 'user'),
    ]
    public function testReassignsUserToAnotherRole(): void
    {
        $user = $this->fixtures->get('user');
        $newRoleId = (int)$this->fixtures->get('role2')->getId();

        $status = $this->commandTester->execute(
            [self::USER_NAME_OPTION => $user->getUserName(), self::ROLE_ID_OPTION => (string)$newRoleId],
            ['interactive' => false]
        );

        $this->assertSame(Cli::RETURN_SUCCESS, $status);
        $this->assertSame([$newRoleId], $this->getAssignedRoleIds($user->getUserName()));
    }

    #[
        DataFixture(RoleFixture::class, as: 'role'),
        DataFixture(UserFixture::class, ['role_id' => '$role.id$'], 'user'),
    ]
    public function testRejectsDuplicateAssignment(): void
    {
        $user = $this->fixtures->get('user');
        $roleId = (int)$this->fixtures->get('role')->getId();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(AssignUserToRole::USER_ALREADY_ASSIGNED_ERROR_MSG, $user->getUserName(), $roleId)
        );

        $this->commandTester->execute(
            [self::USER_NAME_OPTION => $user->getUserName(), self::ROLE_ID_OPTION => (string)$roleId],
            ['interactive' => false]
        );
    }

    #[DataFixture(UserFixture::class, as: 'user')]
    public function testRejectsNonExistingRole(): void
    {
        $nonExistingRoleId = '999999';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(AssignUserToRole::ROLE_DOES_NOT_EXISTS_ERROR_MSG, $nonExistingRoleId));

        $this->commandTester->execute(
            [
                self::USER_NAME_OPTION => $this->fixtures->get('user')->getUserName(),
                self::ROLE_ID_OPTION => $nonExistingRoleId,
            ],
            ['interactive' => false]
        );
    }

    #[
        DataFixture(RoleFixture::class, as: 'role'),
        DataFixture(UserFixture::class, ['role_id' => '$role.id$'], 'owner'),
        DataFixture(UserFixture::class, as: 'user'),
    ]
    public function testRejectsUserRoleRecordAsRole(): void
    {
        $userRoleId = $this->getUserRoleRecordId((int)$this->fixtures->get('owner')->getId());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(AssignUserToRole::ROLE_DOES_NOT_EXISTS_ERROR_MSG, $userRoleId));

        $this->commandTester->execute(
            [
                self::USER_NAME_OPTION => $this->fixtures->get('user')->getUserName(),
                self::ROLE_ID_OPTION => (string)$userRoleId,
            ],
            ['interactive' => false]
        );
    }

    #[DataFixture(RoleFixture::class, as: 'role')]
    public function testRejectsNonExistingUser(): void
    {
        $nonExistingUserName = 'non_existing_user';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(AssignUserToRole::USER_DOES_NOT_EXISTS_ERROR_MSG, $nonExistingUserName));

        $this->commandTester->execute(
            [
                self::USER_NAME_OPTION => $nonExistingUserName,
                self::ROLE_ID_OPTION => (string)$this->fixtures->get('role')->getId(),
            ],
            ['interactive' => false]
        );
    }

    /**
     * @param array $options
     * @param string $message
     */
    #[DataProvider('invalidOptionsDataProvider')]
    public function testRejectsMissingOrInvalidOptions(array $options, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->commandTester->execute($options, ['interactive' => false]);
    }

    /**
     * @return array
     */
    public static function invalidOptionsDataProvider(): array
    {
        return [
            'missing username' => [
                [self::ROLE_ID_OPTION => '1'],
                'The "--username" option is required.',
            ],
            'blank username' => [
                [self::USER_NAME_OPTION => ' ', self::ROLE_ID_OPTION => '1'],
                'The "--username" option is required.',
            ],
            'missing role id' => [
                [self::USER_NAME_OPTION => 'admin'],
                'The "--role-id" option must be a positive integer.',
            ],
            'non-numeric role id' => [
                [self::USER_NAME_OPTION => 'admin', self::ROLE_ID_OPTION => 'abc'],
                'The "--role-id" option must be a positive integer.',
            ],
            'zero role id' => [
                [self::USER_NAME_OPTION => 'admin', self::ROLE_ID_OPTION => '0'],
                'The "--role-id" option must be a positive integer.',
            ],
        ];
    }

    #[
        DataFixture(UserFixture::class, as: 'user'),
        DataFixture(RoleFixture::class, as: 'role'),
    ]
    public function testInteractiveInputAssignsRole(): void
    {
        $user = $this->fixtures->get('user');
        $roleId = (int)$this->fixtures->get('role')->getId();
        $this->commandTester->setInputs([$user->getUserName(), ' ' . $roleId . ' ']);

        $status = $this->commandTester->execute([], ['interactive' => true]);

        $this->assertSame(Cli::RETURN_SUCCESS, $status);
        $this->assertSame([$roleId], $this->getAssignedRoleIds($user->getUserName()));
    }

    #[
        DataFixture(UserFixture::class, as: 'user'),
        DataFixture(RoleFixture::class, as: 'role'),
    ]
    public function testIgnoresRoleRecordOfAnotherUserTypeWithSameId(): void
    {
        $user = $this->fixtures->get('user');
        $roleId = (int)$this->fixtures->get('role')->getId();
        $resource = $this->objectManager->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $table = $resource->getTableName('authorization_role');
        $connection->insert(
            $table,
            [
                'parent_id' => $roleId,
                'tree_level' => 2,
                'sort_order' => 0,
                'role_type' => RoleUser::ROLE_TYPE,
                'user_id' => (int)$user->getId(),
                'user_type' => UserContextInterface::USER_TYPE_INTEGRATION,
                'role_name' => 'integration_collision',
            ]
        );
        $integrationRoleId = (int)$connection->lastInsertId($table);

        try {
            $status = $this->commandTester->execute(
                [self::USER_NAME_OPTION => $user->getUserName(), self::ROLE_ID_OPTION => (string)$roleId],
                ['interactive' => false]
            );

            $this->assertSame(Cli::RETURN_SUCCESS, $status);
            $this->assertSame([$roleId], $this->getAssignedRoleIds($user->getUserName()));
            $this->assertSame(
                (string)UserContextInterface::USER_TYPE_INTEGRATION,
                (string)$connection->fetchOne(
                    $connection->select()->from($table, 'user_type')->where('role_id = ?', $integrationRoleId)
                )
            );
        } finally {
            $connection->delete($table, ['role_id = ?' => $integrationRoleId]);
        }
    }

    /**
     * @param string $userName
     * @return int[]
     */
    private function getAssignedRoleIds(string $userName): array
    {
        /** @var User $user */
        $user = $this->objectManager->get(UserFactory::class)->create();
        $user->loadByUsername($userName);

        return array_map('intval', $this->objectManager->get(UserResource::class)->getRoles($user));
    }

    /**
     * @param int $userId
     * @return int
     */
    private function getUserRoleRecordId(int $userId): int
    {
        $resource = $this->objectManager->get(ResourceConnection::class);
        $connection = $resource->getConnection();

        return (int)$connection->fetchOne(
            $connection->select()
                ->from($resource->getTableName('authorization_role'), 'role_id')
                ->where('user_id = ?', $userId)
                ->where('user_type = ?', UserContextInterface::USER_TYPE_ADMIN)
                ->where('role_type = ?', RoleUser::ROLE_TYPE)
        );
    }
}
