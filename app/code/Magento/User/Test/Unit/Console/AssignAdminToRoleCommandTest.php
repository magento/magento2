<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Console;

use InvalidArgumentException;
use Magento\Authorization\Model\GetAdminRolesList;
use Magento\Framework\Console\Cli;
use Magento\User\Console\AssignAdminToRoleCommand;
use Magento\User\Model\AssignUserToRole;
use Magento\User\Model\GetAdminUserList;
use Magento\User\Model\ResourceModel\User\Collection;
use Magento\User\Model\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssignAdminToRoleCommandTest extends TestCase
{
    /**
     * @var AssignUserToRole|MockObject
     */
    private $assignUserToRole;

    /**
     * @var GetAdminRolesList|MockObject
     */
    private $getAdminRolesList;

    /**
     * @var GetAdminUserList|MockObject
     */
    private $getAdminUserList;

    /**
     * @var CommandTester
     */
    private $commandTester;

    protected function setUp(): void
    {
        $this->assignUserToRole = $this->createMock(AssignUserToRole::class);
        $this->getAdminRolesList = $this->createMock(GetAdminRolesList::class);
        $this->getAdminUserList = $this->createMock(GetAdminUserList::class);
        $command = new AssignAdminToRoleCommand(
            $this->assignUserToRole,
            $this->getAdminRolesList,
            $this->getAdminUserList
        );
        $command->setHelperSet(new HelperSet([new QuestionHelper()]));
        $this->commandTester = new CommandTester($command);
    }

    public function testConfigure(): void
    {
        $command = new AssignAdminToRoleCommand(
            $this->assignUserToRole,
            $this->getAdminRolesList,
            $this->getAdminUserList
        );

        $this->assertSame('admin:user:assign-role', $command->getName());
        $this->assertTrue($command->getDefinition()->hasOption('username'));
        $this->assertTrue($command->getDefinition()->hasOption('role-id'));
    }

    public function testExecutePassesNormalizedOptionsToService(): void
    {
        $this->assignUserToRole->expects($this->once())
            ->method('execute')
            ->with('admin', 5);

        $status = $this->commandTester->execute(
            ['--username' => 'admin', '--role-id' => ' 5 '],
            ['interactive' => false]
        );

        $this->assertSame(Cli::RETURN_SUCCESS, $status);
        $this->assertStringContainsString('User assigned to the role.', $this->commandTester->getDisplay());
    }

    /**
     * @param array $options
     * @param string $message
     */
    #[DataProvider('invalidOptionsDataProvider')]
    public function testExecuteRejectsInvalidOptionsBeforeCallingService(array $options, string $message): void
    {
        $this->assignUserToRole->expects($this->never())->method('execute');
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
            'missing username' => [['--role-id' => '1'], 'The "--username" option is required.'],
            'blank username' => [['--username' => '  ', '--role-id' => '1'], 'The "--username" option is required.'],
            'missing role id' => [['--username' => 'admin'], 'The "--role-id" option must be a positive integer.'],
            'non-numeric role id' => [
                ['--username' => 'admin', '--role-id' => '1abc'],
                'The "--role-id" option must be a positive integer.',
            ],
            'negative role id' => [
                ['--username' => 'admin', '--role-id' => '-1'],
                'The "--role-id" option must be a positive integer.',
            ],
            'zero role id' => [
                ['--username' => 'admin', '--role-id' => '0'],
                'The "--role-id" option must be a positive integer.',
            ],
        ];
    }

    public function testInteractiveInputMatchesIntegerRoleIds(): void
    {
        $this->mockAdminUsers(['admin']);
        $this->getAdminRolesList->method('execute')
            ->willReturn([['role_id' => 1, 'role_name' => 'Administrators'], ['role_id' => 5, 'role_name' => 'Sales']]);
        $this->assignUserToRole->expects($this->once())
            ->method('execute')
            ->with('admin', 5);
        $this->commandTester->setInputs(['admin', '5']);

        $this->assertSame(Cli::RETURN_SUCCESS, $this->commandTester->execute([], ['interactive' => true]));
    }

    public function testInteractiveInputAsksAgainForUnknownRoleId(): void
    {
        $this->mockAdminUsers(['admin']);
        $this->getAdminRolesList->method('execute')
            ->willReturn([['role_id' => '1', 'role_name' => 'Administrators']]);
        $this->assignUserToRole->expects($this->once())
            ->method('execute')
            ->with('admin', 1);
        $this->commandTester->setInputs(['admin', '7', '1']);

        $this->assertSame(Cli::RETURN_SUCCESS, $this->commandTester->execute([], ['interactive' => true]));
        $this->assertStringContainsString(
            sprintf(AssignUserToRole::ROLE_DOES_NOT_EXISTS_ERROR_MSG, '7'),
            $this->commandTester->getDisplay()
        );
    }

    /**
     * @param string[] $userNames
     */
    private function mockAdminUsers(array $userNames): void
    {
        $users = [];
        foreach ($userNames as $userName) {
            $user = $this->createMock(User::class);
            $user->method('getUserName')->willReturn($userName);
            $users[] = $user;
        }
        $collection = $this->createMock(Collection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator($users));
        $this->getAdminUserList->method('execute')->willReturn($collection);
    }
}
