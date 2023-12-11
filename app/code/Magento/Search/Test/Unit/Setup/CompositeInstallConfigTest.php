<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->firstInstallConfigMock = $this->getMockForAbstractClass(InstallConfigInterface::class);
        $this->secondInstallConfigMock = $this->getMockForAbstractClass(InstallConfigInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
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
