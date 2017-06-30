<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Grid;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Setup\Model\Grid\Extension;
use Magento\Setup\Model\PackagesData;
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
     * @var PackagesData|MockObject
     */
    private $packagesDataMock;

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
        $this->packagesDataMock = $this->getMockBuilder(PackagesData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Extension(
            $this->composerInformationMock,
            $this->packagesDataMock
        );
    }

    public function testGetList()
    {
        $this->composerInformationMock->expects($this->any())
            ->method('isPackageInComposerJson')
            ->willReturn(true);
        $this->packagesDataMock->expects($this->once())
            ->method('getInstalledPackages')
            ->willReturn(
                [
                    'magento/package-1' => [
                        'name' => 'magento/package-1',
                        'type' => 'magento2-module',
                        'package_title' => 'packageTitle',
                        'package_type' => 'packageType',
                        'package_link' => 'http://example.com',
                        'version' => '1.0.0'
                    ],
                    'magento/package-2' => [
                        'name' => 'magento/package-2',
                        'type' => 'magento2-module',
                        'package_title' => 'packageTitle',
                        'package_type' => 'packageType',
                        'package_link' => 'http://example.com',
                        'version' => '1.0.1'
                    ],
                ]
            );
        $this->packagesDataMock->expects($this->once())
            ->method('getPackagesForUpdate')
            ->willReturn(
                [
                    'magento/package-1' => []
                ]
            );

        $expected = [
            [
                'name' => 'magento/package-1',
                'type' => 'magento2-module',
                'package_title' => 'packageTitle',
                'package_type' => 'packageType',
                'version' => '1.0.0',
                'update' => true,
                'uninstall' => true,
                'vendor' => 'Magento',
                'package_link' => 'http://example.com'
            ],
            [
                'name' => 'magento/package-2',
                'type' => 'magento2-module',
                'package_title' => 'packageTitle',
                'package_type' => 'packageType',
                'version' => '1.0.1',
                'update' => false,
                'uninstall' => true,
                'vendor' => 'Magento',
                'package_link' => 'http://example.com'
            ],
        ];

        $this->assertEquals($expected, $this->model->getList());
    }
}
