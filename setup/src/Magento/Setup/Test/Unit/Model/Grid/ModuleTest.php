<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model\Grid;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\Grid\Module;
use Magento\Setup\Model\Grid\TypeMapper;
use Magento\Setup\Model\ObjectManagerProvider;
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

    public function setUp()
    {
        $this->moduleData = [
            'magento/sample-module-one' => [
                'name' => 'magento/sample-module-one',
                'type' => 'magento2-module',
                'version' => '1.0.0',
            ],
        ];

        $fullModuleList = [
            'Sample_ModuleOne', 'Sample_ModuleTwo',
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
            ->willReturn($fullModuleList);

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

        $this->packageInfoMock->expects(static::never())
            ->method('getModuleName');

        $this->typeMapperMock->expects(static::exactly(2))
            ->method('map')
            ->willReturnMap([
                ['magento/sample-module-one', 'magento2-module', 'Module'],
                ['Sample_ModuleTwo', 'magento2-module', 'Module'],
            ]);

        $this->packageInfoMock->expects(static::exactly(2))
            ->method('getRequiredBy')
            ->willReturn([]);
        $this->packageInfoMock->expects(static::exactly(2))
            ->method('getPackageName')
            ->willReturnMap([
                    ['Sample_ModuleOne', 'magento/sample-module-one'],
                    ['Sample_ModuleTwo', ''],
                ]);
        $this->packageInfoMock->expects(static::exactly(2))
            ->method('getVersion')
            ->willReturnMap([
                ['Sample_ModuleOne', '1.0.0'],
                ['Sample_ModuleTwo', ''],
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
                'moduleName' => 'Sample_ModuleOne',
                'enable' => true,
                'requiredBy' => [],
            ],
            [
                'name' => Module::UNKNOWN_PACKAGE_NAME,
                'type' => 'Module',
                'version' => Module::UNKNOWN_VERSION,
                'vendor' => 'Sample',
                'moduleName' => 'Sample_ModuleTwo',
                'enable' => true,
                'requiredBy' => [],
            ],
        ];

        static::assertEquals($expected, $this->model->getList());
    }
}
