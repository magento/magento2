<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Grid;

use Composer\Package\RootPackage;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Setup\Model\Grid\Extension;
use Magento\Setup\Model\Grid\TypeMapper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ExtensionTest
 */
class ExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComposerInformation|MockObject
     */
    private $composerInformationMock;

    /**
     * @var TypeMapper|MockObject
     */
    private $typeMapperMock;

    /**
     * Extension
     *
     * @var Extension
     */
    private $model;

    public function setUp()
    {
        $this->composerInformationMock =  $this->getMockBuilder(ComposerInformation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeMapperMock = $this->getMockBuilder(TypeMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->composerInformationMock->expects($this->any())->method('getInstalledMagentoPackages')->willReturn(
            [
                'magento/package-1' => ['name' => 'magento/package-1', 'type' => 'magento2-module', 'version' => '1.0.0'],
                'magento/package-2' => ['name' => 'magento/package-2', 'type' => 'magento2-module', 'version' => '1.0.1'],
                'magento/rootPackage-1' => ['name' => 'magento/rootPackage-1', 'type' => 'magento2-module', 'version' => '1.0.0']
            ]
        );

        $rootPackage = $this->getMock(RootPackage::class, [], ['magento/project', '2.1.0', '2']);

        $rootPackage->expects($this->once())
            ->method('getRequires')
            ->willReturn(['magento/rootPackage-1' => '1.0.0']);
        $this->composerInformationMock
            ->expects($this->any())
            ->method('getRootPackage')
            ->willReturn($rootPackage);

        $this->model = new Extension(
            $this->composerInformationMock,
            $this->typeMapperMock
        );
    }

    public function testGetList()
    {
        $this->typeMapperMock->expects($this->once())
            ->method('map')
            ->willReturn('Extension');

        $this->model->setLastSyncData([
            "packages" => [
                'magento/rootPackage-1' => [
                    'name' => 'magento/rootPackage-1',
                    'type' => 'magento2-module',
                    'version' => '1.0.1',
                    'latestVersion' => '1.0.2'
                ]
            ],
        ]);

        $expected = [
            [
                'name' => 'magento/rootPackage-1',
                'type' => 'Extension',
                'version' => '1.0.0',
                'update' => true,
                'uninstall' => true,
                'vendor' => 'Magento',
            ]
        ];

        $this->assertEquals($expected, $this->model->getList());
    }

    public function testGetInstalledExtensions()
    {
        $this->assertEquals(
            [
                'magento/rootPackage-1' => ['name' => 'magento/rootPackage-1', 'type' => 'magento2-module', 'version'=> '1.0.0'],
            ],
            $this->model->getInstalledExtensions()
        );
    }
}
