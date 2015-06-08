<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\IndexersStatesApplyFixture;

class IndexersStatesApplyFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMockBuilder('\Magento\Setup\Fixtures\FixtureModel')->disableOriginalConstructor()->getMock();
    }

    public function testExecute()
    {
        $cacheInterfaceMock = $this->getMockBuilder('Magento\Framework\App\CacheInterface')->disableOriginalConstructor()->getMock();

        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')->disableOriginalConstructor()->getMock();
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturn($cacheInterfaceMock);

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(array(
                'indexer' => ['id' => 1]
            ));
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $indexersStatesApplyFixture = new IndexersStatesApplyFixture($this->fixtureModelMock);
        $indexersStatesApplyFixture->execute();
    }

    public function testGetActionTitle()
    {
        $indexersStatesApplyFixture = new IndexersStatesApplyFixture($this->fixtureModelMock);
        $this->assertSame('Indexers Mode Changes', $indexersStatesApplyFixture->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $indexersStatesApplyFixture = new IndexersStatesApplyFixture($this->fixtureModelMock);
        $this->assertSame([], $indexersStatesApplyFixture->introduceParamLabels());
    }
}
