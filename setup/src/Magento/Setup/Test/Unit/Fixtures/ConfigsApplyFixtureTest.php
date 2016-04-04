<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\ConfigsApplyFixture;

class ConfigsApplyFixtureTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\ConfigsApplyFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMock('\Magento\Setup\Fixtures\FixtureModel', [], [], '', false);

        $this->model = new ConfigsApplyFixture($this->fixtureModelMock);
    }
    public function testExecute()
    {
        $cacheMock = $this->getMock('\Magento\Framework\App\Cache', [], [], '', false);

        $valueMock = $this->getMock('\Magento\Framework\App\Config', [], [], '', false);

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($cacheMock));

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(['config' => $valueMock]));
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $configMock = $this->getMock('\Magento\Framework\App\Config\ValueInterface', [], [], '', false);
        $configMock->expects($this->never())->method('save');

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->willReturn($configMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Config Changes', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([], $this->model->introduceParamLabels());
    }
}
