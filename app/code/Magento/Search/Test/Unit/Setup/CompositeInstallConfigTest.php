<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Setup;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Setup\CompositeInstallConfig;
use Magento\Search\Setup\InstallConfigInterface;
use PHPUnit\Framework\TestCase;

class CompositeInstallConfigTest extends TestCase
{
    public function testConfigure()
    {
        $firstInstallConfig = $this->getMockBuilder(InstallConfigInterface::class)->getMock();
        $secondInstallConfig = $this->getMockBuilder(InstallConfigInterface::class)->getMock();
        $objectManager = new ObjectManager($this);

        /** @var CompositeInstallConfig $compositeInstallConfig */
        $compositeInstallConfig = $objectManager->getObject(
            CompositeInstallConfig::class,
            [
                'installConfigList' => [
                    'first' => $firstInstallConfig,
                    'second' => $secondInstallConfig
                ]
            ]
        );

        $testInput = [
            'search-engine' => 'second',
            'test-option' => 'testValue'
        ];

        $firstInstallConfig->expects($this->never())->method('configure');
        $secondInstallConfig->expects($this->once())->method('configure')->with($testInput);

        $compositeInstallConfig->configure($testInput);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Unable to configure search engine: other-engine
     */
    public function testConfigureInvalidSearchEngine()
    {
        $firstInstallConfig = $this->getMockBuilder(InstallConfigInterface::class)->getMockForAbstractClass();
        $secondInstallConfig = $this->getMockBuilder(InstallConfigInterface::class)->getMockForAbstractClass();
        $objectManager = new ObjectManager($this);

        /** @var CompositeInstallConfig $compositeInstallConfig */
        $compositeInstallConfig = $objectManager->getObject(
            CompositeInstallConfig::class,
            [
                'installConfigList' => [
                    'first' => $firstInstallConfig,
                    'second' => $secondInstallConfig
                ]
            ]
        );

        $compositeInstallConfig->configure([
            'search-engine' => 'other-engine',
            'test-option' => 'testValue'
        ]);
    }
}
