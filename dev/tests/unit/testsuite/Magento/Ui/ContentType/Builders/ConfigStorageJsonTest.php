<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Ui\ContentType\Builders;

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
        $result = [
            'config' => ['components' => [$name => $data], 'globalData' => ['globalData']],
            'meta' => null,
            'name' => $name,
            'parent_name' => $parentName,
            'data' => null,
            'dump' => ['extenders' => []]
        ];

        $rootComponentMock = $this->getMock(
            'Magento\Ui\Configuration',
            ['getName', 'getParentName', 'getData'],
            [],
            '',
            false
        );
        $storageMock = $this->getMock(
            'Magento\Ui\ConfigurationStorage',
            ['getComponentsData', 'getGlobalData', 'getMeta', 'getData'],
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

        $this->assertEquals(json_encode($result), $this->builder->toJson($storageMock, $parentName));
    }

    public function testToJsonNoParentName()
    {
        $this->builder = new ConfigStorageJson();
        $data = [];
        $result = [
            'config' => ['components' => ['name' => $data], 'globalData' => ['globalData']],
            'meta' => null,
            'data' => null,
            'dump' => ['extenders' => []]
        ];
        $componentsMock = $this->getMock('Magento\Ui\Configuration', ['getData'], [], '', false);
        $storageMock = $this->getMock(
            'Magento\Ui\ConfigurationStorage',
            ['getComponentsData', 'getGlobalData', 'getMeta', 'getData'],
            [],
            '',
            false
        );

        $storageMock->expects($this->once())->method('getComponentsData')->will($this->returnValue($componentsMock));
        $componentsMock->expects($this->any())->method('getData')->willReturn($data);

        $storageMock->expects($this->once())->method('getMeta')->will($this->returnValue($result['meta']));
        $storageMock->expects($this->once())->method('getData')->will($this->returnValue($result['data']));
        $storageMock->expects($this->once())->method('getGlobalData')->willReturn($result['config']);

        $this->assertEquals(json_encode($result), $this->builder->toJson($storageMock));
    }
}
