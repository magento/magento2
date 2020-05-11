<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\TemplateHintsStatusCommand;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;

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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->reinitableConfigMock = $this->getMockForAbstractClass(ReinitableConfigInterface::class);

        $this->command = new TemplateHintsStatusCommand(
            $this->scopeConfigMock,
            $this->reinitableConfigMock
        );
    }

    /**
     * Verify ScopeConfigInterface instance
     */
    public function testScopeConfigInterfaceInstance()
    {
        $this->assertInstanceOf(ScopeConfigInterface::class, $this->scopeConfigMock);
    }

    /**
     * Verify ReinitableConfigInterface instance
     */
    public function testReinitableConfigInterfaceInstance()
    {
        $this->assertInstanceOf(ReinitableConfigInterface::class, $this->reinitableConfigMock);
    }

    /**
     * Verify TemplateHintsStatusCommand instance
     */
    public function testCommandInstance()
    {
        $this->assertInstanceOf(TemplateHintsStatusCommand::class, $this->command);
    }
}
