<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Console;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Console\UnlockAdminAccountCommand;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for StartConsumerCommand
 */
class UnlockAdminAccountCommandTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @var UnlockAdminAccountCommand
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        parent::setUp();
    }

    /**
     * Test configure() method implicitly via construct invocation.
     *
     * @return void
     */
    public function testConfigure()
    {
        $this->command = $this->objectManager
            ->getObject(UnlockAdminAccountCommand::class);

        $this->assertEquals(UnlockAdminAccountCommand::COMMAND_ADMIN_ACCOUNT_UNLOCK, $this->command->getName());
        $this->assertEquals(UnlockAdminAccountCommand::COMMAND_DESCRIPTION, $this->command->getDescription());
        $this->command->getDefinition()->getArgument(UnlockAdminAccountCommand::ARGUMENT_ADMIN_USERNAME);
        $this->assertStringContainsString(
            'This command unlocks an admin account by its username',
            $this->command->getHelp()
        );
    }
}
