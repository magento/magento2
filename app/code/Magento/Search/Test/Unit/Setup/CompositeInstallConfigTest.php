<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Setup;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Setup\CompositeInstallConfig;
use Magento\Search\Setup\InstallConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeInstallConfigTest extends TestCase
{
    /**
     * @var CompositeInstallConfig
     */
    private $compositeInstallConfig;

    /**
     * @var InstallConfigInterface|MockObject
     */
    private $firstInstallConfigMock;

    /**
     * @var InstallConfigInterface|MockObject
     */
    private $secondInstallConfigMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    protected function setup(): void
    {
        $objectManager = new ObjectManager($this);
        $this->firstInstallConfigMock = $this->createMock(InstallConfigInterface::class);
        $this->secondInstallConfigMock = $this->createMock(InstallConfigInterface::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->compositeInstallConfig = $objectManager->getObject(
            CompositeInstallConfig::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'installConfigList' => [
                    'first' => $this->firstInstallConfigMock,
                    'second' => $this->secondInstallConfigMock
                ]
            ]
        );
    }

    public function testConfigure()
    {
        $testInput = [
            'search-engine' => 'second',
            'test-option' => 'testValue'
        ];

        $this->firstInstallConfigMock->expects($this->never())->method('configure');
        $this->secondInstallConfigMock->expects($this->once())->method('configure')->with($testInput);

        $this->compositeInstallConfig->configure($testInput);
    }

    public function testConfigureEmptyInput()
    {
        $this->firstInstallConfigMock->expects($this->never())->method('configure');
        $this->secondInstallConfigMock->expects($this->never())->method('configure');

        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/search/engine')
            ->willReturn('second');

        $this->compositeInstallConfig->configure([]);
    }
}
