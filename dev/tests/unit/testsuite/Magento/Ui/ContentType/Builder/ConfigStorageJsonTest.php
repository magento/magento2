<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\ContentType\Builder;

/**
 * Class ConfigStorageJsonTest
 */
class ConfigStorageJsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigStorageJson
     */
    protected $builder;

    public function testToJson()
    {
        $this->builder = new ConfigStorageJson();
        $name = 'name';
        $data = [];
        $parentName = 'parentName';
        $dataSource = [
            'data' => null,
        ];
        $result = [
            'config' => ['components' => [$name => $data], 'globalData' => ['globalData']],
            'meta' => null,
            'name' => $name,
            'parent_name' => $parentName,
            'data' => null,
            'dump' => ['extenders' => []],
        ];

        $rootComponentMock = $this->getMock(
            'Magento\Ui\Context\Configuration',
            ['getName', 'getParentName', 'getData'],
            [],
            '',
            false
        );
        $storageMock = $this->getMock(
            'Magento\Ui\Context\ConfigurationStorage',
            ['getComponentsData', 'getGlobalData', 'getMeta', 'getDataSource'],
            [],
            '',
            false
        );

        $storageMock->expects($this->once())
            ->method('getComponentsData')
            ->with($parentName)
            ->will($this->returnValue($rootComponentMock));
        $rootComponentMock->expects($this->any())->method('getName')->willReturn($result['name']);
        $rootComponentMock->expects($this->once())->method('getParentName')->willReturn($result['parent_name']);
        $rootComponentMock->expects($this->once())
            ->method('getData')
            ->willReturn($data);
        $storageMock->expects($this->once())->method('getGlobalData')->willReturn($result['config']);
        $storageMock->expects($this->once())->method('getDataSource')->will($this->returnValue($dataSource));

        $this->assertEquals(json_encode($result), $this->builder->toJson($storageMock, $parentName));
    }

    public function testToJsonNoParentName()
    {
        $this->builder = new ConfigStorageJson();
        $data = [];
        $dataSource = [
            'data' => $data,
        ];
        $result = [
            'config' => ['components' => ['name' => $data], 'globalData' => ['globalData']],
            'meta' => null,
            'data' => [],
            'dump' => ['extenders' => []],
        ];
        $componentsMock = $this->getMock('Magento\Ui\Context\Configuration', ['getData'], [], '', false);
        $storageMock = $this->getMock(
            'Magento\Ui\Context\ConfigurationStorage',
            ['getComponentsData', 'getGlobalData', 'getMeta', 'getDataSource'],
            [],
            '',
            false
        );

        $storageMock->expects($this->once())->method('getComponentsData')->will($this->returnValue($componentsMock));
        $componentsMock->expects($this->any())->method('getData')->willReturn($data);

        $storageMock->expects($this->once())->method('getMeta')->will($this->returnValue($result['meta']));
        $storageMock->expects($this->once())->method('getDataSource')->will($this->returnValue($dataSource));
        $storageMock->expects($this->once())->method('getGlobalData')->willReturn($result['config']);

        $this->assertEquals(json_encode($result), $this->builder->toJson($storageMock));
    }
}
