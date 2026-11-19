<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Authorization\Test\Integration\Console\Command;

use Magento\Authorization\Command\AdminRolesListCommand;
use Magento\Authorization\Test\Fixture\Role as RoleFixture;
use Magento\Framework\Console\Cli;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[AppArea('adminhtml')]
class AdminRolesListCommandTest extends TestCase
{
    #[DataFixture(RoleFixture::class, ['role_name' => 'test_role'], 'role')]
    public function testExecuteListsRoleGroups(): void
    {
        $role = DataFixtureStorageManager::getStorage()->get('role');
        $command = Bootstrap::getObjectManager()->get(AdminRolesListCommand::class);
        $commandTester = new CommandTester($command);

        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->execute([]));
        $display = $commandTester->getDisplay();
        $this->assertStringContainsString('Role ID', $display);
        $this->assertStringContainsString('Role Name', $display);
        $this->assertMatchesRegularExpression('/\|\s*' . (int)$role->getId() . '\s*\|\s*test_role\s*\|/', $display);
    }
}
