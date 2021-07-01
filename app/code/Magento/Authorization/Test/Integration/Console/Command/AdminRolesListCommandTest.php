<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorization\Test\Integration\Console\Command;

use Magento\Authorization\Command\AdminRolesListCommand;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AdminRolesListCommandTes
 */
class AdminRolesListCommandTest extends TestCase
{
    /**
     * @magentoDataFixture ./../../../../app/code/Magento/Authorization/Test/Integration/_files/test_role.php
     */
    public function testExecute(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $command = $objectManager->get(AdminRolesListCommand::class);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $this->assertStringContainsString('Role ID ', $commandTester->getDisplay());
        $this->assertStringContainsString('Role Name', $commandTester->getDisplay());
        $this->assertStringContainsString('test_role', $commandTester->getDisplay());
    }
}
