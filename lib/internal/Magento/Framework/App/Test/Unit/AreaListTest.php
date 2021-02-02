<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit;

use \Magento\Framework\App\AreaList;

class AreaListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\AreaList
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Area\FrontNameResolverFactory
     */
    protected $_resolverFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_resolverFactory = $this
            ->createMock(\Magento\Framework\App\Area\FrontNameResolverFactory::class);
    }

    public function testGetCodeByFrontNameWhenAreaDoesNotContainFrontName()
    {
        $expected = 'expectedFrontName';
        $this->_model = new \Magento\Framework\App\AreaList(
            $this->objectManagerMock,
            $this->_resolverFactory,
            ['testArea' => ['frontNameResolver' => 'testValue']],
            $expected
        );

        $resolverMock = $this->createMock(\Magento\Framework\App\Area\FrontNameResolverInterface::class);
        $this->_resolverFactory->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            'testValue'
        )->willReturn(
            $resolverMock
        );

        $actual = $this->_model->getCodeByFrontName('testFrontName');
        $this->assertEquals($expected, $actual);
    }

    public function testGetCodeByFrontNameReturnsAreaCode()
    {
        $expected = 'testArea';
        $this->_model = new \Magento\Framework\App\AreaList(
            $this->objectManagerMock,
            $this->_resolverFactory,
            ['testArea' => ['frontName' => 'testFrontName']],
            $expected
        );

        $actual = $this->_model->getCodeByFrontName('testFrontName');
        $this->assertEquals($expected, $actual);
    }

    public function testGetFrontNameWhenAreaCodeAndFrontNameAreSet()
    {
        $expected = 'testFrontName';
        $this->_model = new \Magento\Framework\App\AreaList(
            $this->objectManagerMock,
            $this->_resolverFactory,
            ['testAreaCode' => ['frontName' => 'testFrontName']],
            $expected
        );

        $actual = $this->_model->getFrontName('testAreaCode');
        $this->assertEquals($expected, $actual);
    }

    public function testGetFrontNameWhenAreaCodeAndFrontNameArentSet()
    {
        $model = new \Magento\Framework\App\AreaList($this->objectManagerMock, $this->_resolverFactory);
        $code = 'testAreaCode';
        $this->assertNull($model->getCodeByFrontName($code));
        $this->assertNull($model->getFrontName($code));
        $this->assertSame([], $model->getCodes());
        $this->assertNull($model->getDefaultRouter($code));
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\App\AreaInterface::class, ['areaCode' => $code])
            ->willReturn('test');
        $this->assertSame('test', $model->getArea($code));
    }

    public function testGetFrontNameWhenFrontNameIsInvalid() : void
    {
        $this->_model = new AreaList(
            $this->objectManagerMock,
            $this->_resolverFactory,
            [
                'testAreaCode' => []
            ]
        );

        $this->assertNull($this->_model->getFrontName('0'));
    }

    public function testGetCodes()
    {
        $areas = ['area1' => 'value1', 'area2' => 'value2'];
        $this->_model = new \Magento\Framework\App\AreaList(
            $this->objectManagerMock,
            $this->_resolverFactory,
            $areas,
            ''
        );

        $expected = array_keys($areas);
        $actual = $this->_model->getCodes();
        $this->assertEquals($expected, $actual);
    }

    public function testGetDefaultRouter()
    {
        $areas = ['area1' => ['router' => 'value1'], 'area2' => 'value2'];
        $this->_model = new \Magento\Framework\App\AreaList(
            $this->objectManagerMock,
            $this->_resolverFactory,
            $areas,
            ''
        );

        $this->assertEquals($this->_model->getDefaultRouter('area1'), $areas['area1']['router']);
        $this->assertNull($this->_model->getDefaultRouter('area2'));
    }

    public function testGetArea()
    {
        /** @var \Magento\Framework\ObjectManagerInterface $objectManagerMock */
        $objectManagerMock = $this->getObjectManagerMockGetArea();
        $areas = ['area1' => ['router' => 'value1'], 'area2' => 'value2'];
        $this->_model = new AreaList(
            $objectManagerMock,
            $this->_resolverFactory,
            $areas,
            ''
        );

        $this->assertEquals($this->_model->getArea('testArea'), 'ok');
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getObjectManagerMockGetArea()
    {
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock
            ->expects($this->any())
            ->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\App\AreaInterface::class),
                $this->equalTo(['areaCode' => 'testArea'])
            )
            ->willReturn('ok');

        return $objectManagerMock;
    }
}
