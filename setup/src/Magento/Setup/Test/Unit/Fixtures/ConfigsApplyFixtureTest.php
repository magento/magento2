<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMockBuilder('\Magento\Setup\Fixtures\FixtureModel')
            ->disableOriginalConstructor()
            ->getMock();
    }
    public function testExecute()
    {
        $cacheMock = $this->getMockBuilder('\Magento\Framework\App\Cache')->disableOriginalConstructor()->getMock();

        $valueMock = $this->getMockBuilder('\Magento\Framework\App\Config')->disableOriginalConstructor()->getMock();

        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
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

        $configsApplyFixture = new ConfigsApplyFixture($this->fixtureModelMock);
        $configsApplyFixture->execute();
    }

    public function testGetActionTitle()
    {
        $configsApplyFixture = new ConfigsApplyFixture($this->fixtureModelMock);
        $this->assertSame('Config Changes', $configsApplyFixture->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $configsApplyFixture = new ConfigsApplyFixture($this->fixtureModelMock);
        $this->assertSame([], $configsApplyFixture->introduceParamLabels());
    }
}
