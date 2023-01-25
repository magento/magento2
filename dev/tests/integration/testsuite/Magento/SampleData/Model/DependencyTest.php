<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Model;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Config\Composer\PackageFactory;

class DependencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SampleData\Model\Dependency
     */
    private $model;

    /**
     * @var ComposerInformation|\PHPUnit\Framework\MockObject\MockObject
     */
    private $composerInformationMock;

    /**
     * @var ComponentRegistrar|\PHPUnit\Framework\MockObject\MockObject
     */
    private $componentRegistrarMock;

    protected function setUp(): void
    {
        $this->composerInformationMock = $this->getMockBuilder(ComposerInformation::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
        $this->componentRegistrarMock = $this->getMockBuilder(ComponentRegistrar::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create(
            \Magento\SampleData\Model\Dependency::class,
            [
                'composerInformation' => $this->composerInformationMock,
                'filesystem' => $objectManager->get(Filesystem::class),
                'packageFactory' => $objectManager->get(PackageFactory::class),
                'componentRegistrar' => $this->componentRegistrarMock
            ]
        );
    }

    public function testGetSampleDataPackages()
    {
        $this->composerInformationMock->expects($this->once())
            ->method('getSuggestedPackages')
            ->willReturn([]);
        $this->componentRegistrarMock->expects($this->once())
            ->method('getPaths')
            ->with(ComponentRegistrar::MODULE)
            ->willReturn([
                __DIR__ . '/../_files/Modules/FirstModule',
                __DIR__ . '/../_files/Modules/SecondModule',
                __DIR__ . '/../_files/Modules/ThirdModule',
                __DIR__ . '/../_files/Modules/FourthModule'
            ]);

        $this->assertSame(
            ['magento/module-first-sample-data' => '777.7.*'],
            $this->model->getSampleDataPackages()
        );
    }
}
