<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model\Grid;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Setup\Model\Grid\TypeMapper;
use Composer\Package\RootPackageInterface;

/**
 * Class TypeMapperTest
 */
class TypeMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComposerInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerInformationMock;

    /**
     * Model
     *
     * @var TypeMapper
     */
    private $model;

    public function setUp()
    {
        $this->composerInformationMock = $this->getMockBuilder(ComposerInformation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->typeMapperMock = $this->getMockBuilder(TypeMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new TypeMapper(
            $this->composerInformationMock
        );
    }

    /**
     * @param string $packageName
     * @param string $packageType
     * @param string $expected
     * @dataProvider mapDataProvider
     */
    public function testMap($packageName, $packageType, $expected)
    {
        $rootPackageMock = $this->getMock(RootPackageInterface::class);
        $rootPackageMock->expects(static::once())
            ->method('getRequires')
            ->willReturn(
                ['magento/sample-module-one' => '']
            );

        $this->composerInformationMock->expects(static::once())
            ->method('getRootPackage')
            ->willReturn($rootPackageMock);

        static::assertEquals(
            $expected,
            $this->model->map($packageName, $packageType)
        );
    }

    public function mapDataProvider()
    {
        return [
            ['magento/sample-module-one', ComposerInformation::MODULE_PACKAGE_TYPE, TypeMapper::EXTENSION_PACKAGE_TYPE],
            ['magento/sample-module-two', ComposerInformation::MODULE_PACKAGE_TYPE, 'Module'],
            ['magento/sample-module-two', 'undefined', TypeMapper::UNDEFINED_PACKAGE_TYPE]
        ];
    }
}
