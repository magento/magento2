<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PasswordManagement\Test\Unit\Console;

use Magento\PasswordManagement\Console\UnlockAdminAccountCommand;

/**
 * Unit tests for StartConsumerCommand
 */
class UnlockAdminAccountCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    /**
     * @var UnlockAdminAccountCommand
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
            ->getObject('Magento\PasswordManagement\Console\UnlockAdminAccountCommand');

        $this->assertEquals(UnlockAdminAccountCommand::COMMAND_ADMIN_ACCOUT_UNLOCK, $this->command->getName());
        $this->assertEquals(UnlockAdminAccountCommand::COMMAND_DESCRIPTION, $this->command->getDescription());
        $this->command->getDefinition()->getArgument(UnlockAdminAccountCommand::ARGUMENT_ADMIN_USERNAME);
        $this->assertContains('This command unlocks admin by its username', $this->command->getHelp());
    }
}
