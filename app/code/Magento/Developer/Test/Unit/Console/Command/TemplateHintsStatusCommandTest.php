<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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

    /**
     * TemplateHintsStatusCommandTest setup
     */
    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->reinitableConfigMock = $this->getMockForAbstractClass(ReinitableConfigInterface::class);

        $this->command = new TemplateHintsStatusCommand(
            $this->scopeConfigMock,
            $this->reinitableConfigMock
        );

    }

    /**
     * Test execution
     */
    public function testExecute()
    {
        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $this->assertEquals(
            0,
            $tester->getStatusCode()
        );
    }
}