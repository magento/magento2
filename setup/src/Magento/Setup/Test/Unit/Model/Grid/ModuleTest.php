<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model\Grid;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\Grid\Module;
use Magento\Setup\Model\Grid\TypeMapper;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Setup\Model\PackagesData;

/**
 * Class ModuleTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComposerInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerInformationMock;

    /**
     * @var FullModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fullModuleListMock;

    /**
     * @var ModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleListMock;

    /**
     * @var PackageInfoFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packageInfoFactoryMock;

    /**
     * Module package info
     *
     * @var PackageInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packageInfoMock;
    
    /**
     * @var ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProvider;

    /**
     * @var TypeMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeMapperMock;

    /**
     * @var PackagesData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packagesDataMock;

    /**
     * Model
     *
     * @var Module
     */
    private $model;
    
    /**
     * @var array
     */
    private $moduleData = [];

    /**
     * @var array
     */
    private $allComponentData = [];

    public function setUp()
    {
        $this->moduleData = [
            'magento/sample-module-one' => [
                'name' => 'magento/sample-module-one',
                'type' => 'magento2-module',
                'version' => '1.0.0'
            ]
        ];
        $this->allComponentData = [
            'magento/sample-module-one' => [
                'name' => 'magento/sample-module-one',
                'type' => 'magento2-module',
                'version' => '1.0.0'
            ],
            'magento/sample-module-two' => [
                'name' => 'magento/sample-module-two',
                'type' => 'magento2-module',
                'version' => '1.0.0'
            ]
        ];

        $this->composerInformationMock = $this->getMockBuilder(ComposerInformation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerProvider = $this->getMockBuilder(ObjectManagerProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->packageInfoFactoryMock = $this->getMockBuilder(PackageInfoFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->moduleListMock = $this->getMockBuilder(ModuleList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->moduleListMock->expects(static::any())
            ->method('has')
            ->willReturn(true);

        $this->fullModuleListMock = $this->getMockBuilder(FullModuleList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fullModuleListMock->expects(static::any())
            ->method('getNames')
            ->willReturn(array_keys($this->allComponentData));
        
        $this->packageInfoMock = $this->getMockBuilder(PackageInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->typeMapperMock = $this->getMockBuilder(TypeMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->packagesDataMock = $this->getMockBuilder(PackagesData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Module(
            $this->composerInformationMock,
            $this->fullModuleListMock,
            $this->moduleListMock,
            $this->objectManagerProvider,
            $this->typeMapperMock,
            $this->packagesDataMock
        );
    }

    public function testGetList()
    {
        $objectManager = $this->getMock(ObjectManagerInterface::class);
        $this->objectManagerProvider->expects($this->once())
            ->method('get')
            ->willReturn($objectManager);
        $objectManager->expects(static::once())
            ->method('get')
            ->willReturnMap([
                [PackageInfoFactory::class, $this->packageInfoFactoryMock],
            ]);
        $this->packageInfoFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->packageInfoMock);

        $this->packageInfoMock->expects(static::exactly(2))
            ->method('getModuleName')
            ->willReturnMap([
                ['magento/sample-module-one', 'Sample_Module_One'],
                ['magento/sample-module-two', 'Sample_Module_Two'],
            ]);

        $this->typeMapperMock->expects(static::exactly(2))
            ->method('map')
            ->willReturnMap([
                ['magento/sample-module-one', 'magento2-module', 'Module'],
                ['magento/sample-module-two', 'magento2-module', 'Module'],
            ]);

        $this->packageInfoMock->expects(static::exactly(2))
            ->method('getRequiredBy')
            ->willReturn([]);
        $this->packageInfoMock->expects(static::exactly(2))
            ->method('getPackageName')
            ->willReturnMap([
                    ['magento/sample-module-one', $this->allComponentData['magento/sample-module-one']['name']],
                    ['magento/sample-module-two', $this->allComponentData['magento/sample-module-two']['name']],
                ]);
        $this->packageInfoMock->expects(static::exactly(2))
            ->method('getVersion')
            ->willReturnMap([
                ['magento/sample-module-one', $this->allComponentData['magento/sample-module-one']['version']],
                ['magento/sample-module-two', $this->allComponentData['magento/sample-module-two']['version']],
            ]);
        $this->moduleListMock->expects(static::exactly(2))
            ->method('has')
            ->willReturn(true);
        $this->composerInformationMock->expects(static::once())
            ->method('getInstalledMagentoPackages')
            ->willReturn($this->moduleData);

        $expected = [
            [
                'name' => 'magento/sample-module-one',
                'type' => 'Module',
                'version' => '1.0.0',
                'vendor' => 'Magento',
                'moduleName' => 'Sample_Module_One',
                'enable' => true,
                'requiredBy' => []
            ],
            [
                'name' => 'magento/sample-module-two',
                'type' => 'Module',
                'version' => '1.0.0',
                'vendor' => 'Magento',
                'moduleName' => 'Sample_Module_Two',
                'enable' => true,
                'requiredBy' => []
            ]
        ];

        static::assertEquals($expected, $this->model->getList());
    }
}
