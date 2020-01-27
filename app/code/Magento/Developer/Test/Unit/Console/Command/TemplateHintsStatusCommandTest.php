<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Developer\Console\Command\TemplateHintsStatusCommand;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;

/**
 * Class TemplateHintsStatusCommandTest
 *
 * Tests dev:template-hints:status command.
 */
class TemplateHintsStatusCommandTest extends TestCase
{
    /**
     * @var TemplateHintsStatusCommand
     */
    private $command;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigMock;
    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfigMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->reinitableConfigMock = $this->getMockForAbstractClass(ReinitableConfigInterface::class);

        $this->command = new TemplateHintsStatusCommand(
            $this->scopeConfigMock,
            $this->reinitableConfigMock
        );

    }

    public function testExecute()
    {
        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $this->assertContains(
            'disabled',
            $tester->getDisplay()
        );

        $this->assertEquals(
            0,
            $tester->getStatusCode()
        );
    }
}