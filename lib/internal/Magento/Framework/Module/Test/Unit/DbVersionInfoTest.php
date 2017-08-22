<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit;

use \Magento\Framework\Module\DbVersionInfo;

class DbVersionInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DbVersionInfo
     */
    private $dbVersionInfo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleList;

    /**
     * @var \Magento\Framework\Module\ResourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleResource;

    protected function setUp()
    {
        $this->moduleList = $this->getMockForAbstractClass(\Magento\Framework\Module\ModuleListInterface::class);
        $this->moduleList->expects($this->any())
            ->method('getOne')
            ->will($this->returnValueMap([
                        ['Module_One', ['name' => 'Module_One', 'setup_version' => '1']],
                        ['Module_Two', ['name' => 'Module_Two', 'setup_version' => '2']],
                        ['Module_No_Schema', []],
                    ]));
        $this->moduleList->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['Module_One', 'Module_Two']));

        $this->_outputConfig = $this->getMockForAbstractClass(\Magento\Framework\Module\Output\ConfigInterface::class);
        $this->moduleResource = $this->getMockForAbstractClass(\Magento\Framework\Module\ResourceInterface::class);

        $this->dbVersionInfo = new DbVersionInfo(
            $this->moduleList,
            $this->moduleResource
        );
    }

    /**
     * @param string $moduleName
     * @param string|bool $dbVersion
     * @param bool $expectedResult
     *
     * @dataProvider isDbUpToDateDataProvider
     */
    public function testIsDbSchemaUpToDate($moduleName, $dbVersion, $expectedResult)
    {
        $this->moduleResource->expects($this->once())
            ->method('getDbVersion')
            ->with($moduleName)
            ->will($this->returnValue($dbVersion));
        $this->assertEquals(
            $expectedResult,
            $this->dbVersionInfo->isSchemaUpToDate($moduleName)
        );
    }

    /**
     * @param string $moduleName
     * @param string|bool $dbVersion
     * @param bool $expectedResult
     *
     * @dataProvider isDbUpToDateDataProvider
     */
    public function testIsDbDataUpToDate($moduleName, $dbVersion, $expectedResult)
    {
        $this->moduleResource->expects($this->once())
            ->method('getDataVersion')
            ->with($moduleName)
            ->will($this->returnValue($dbVersion));
        $this->assertEquals(
            $expectedResult,
            $this->dbVersionInfo->isDataUpToDate($moduleName)
        );
    }

    /**
     * @return array
     */
    public function isDbUpToDateDataProvider()
    {
        return [
            'version in config == version in db' => ['Module_One', '1', true],
            'version in config < version in db' => [
                'Module_One',
                '2',
                false
            ],
            'version in config > version in db' => [
                'Module_Two',
                '1',
                false
            ],
            'no version in db' => [
                'Module_One',
                false,
                false
            ],
        ];
    }

    public function testGetDbVersionErrors()
    {
        $this->moduleResource->expects($this->any())
            ->method('getDataVersion')
            ->will($this->returnValue(2));
        $this->moduleResource->expects($this->any())
            ->method('getDbVersion')
            ->will($this->returnValue(2));

        $expectedErrors = [
            [
                DbVersionInfo::KEY_MODULE => 'Module_One',
                DbVersionInfo::KEY_CURRENT => '2',
                DbVersionInfo::KEY_REQUIRED => '1',
                DbVersionInfo::KEY_TYPE => 'schema',
            ],
            [
                DbVersionInfo::KEY_MODULE => 'Module_One',
                DbVersionInfo::KEY_CURRENT => '2',
                DbVersionInfo::KEY_REQUIRED => '1',
                DbVersionInfo::KEY_TYPE => 'data',
            ]
        ];
        $this->assertEquals($expectedErrors, $this->dbVersionInfo->getDbVersionErrors());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Setup version for module 'Module_No_Schema' is not specified
     */
    public function testIsDbSchemaUpToDateException()
    {
        $this->dbVersionInfo->isSchemaUpToDate('Module_No_Schema');
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Setup version for module 'Module_No_Schema' is not specified
     */
    public function testIsDbDataUpToDateException()
    {
        $this->dbVersionInfo->isDataUpToDate('Module_No_Schema');
    }
}
