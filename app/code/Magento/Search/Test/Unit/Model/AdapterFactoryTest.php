<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Search\Model\AdapterFactory;

class AdapterFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AdapterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterFactory;

    /**
     * @var ObjectManagerInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var EngineResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineResolverMock;

    protected function setUp()
    {
        $this->engineResolverMock = $this->getMockBuilder(EngineResolverInterface::class)
            ->getMockForAbstractClass();

        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->adapterFactory = new AdapterFactory(
            $this->objectManager,
            ['ClassName' => 'ClassName'],
            $this->engineResolverMock
        );
    }

    public function testCreate()
    {
        $this->engineResolverMock->expects($this->once())->method('getCurrentSearchEngine')
            ->will($this->returnValue('ClassName'));

        $adapter = $this->getMockBuilder(\Magento\Framework\Search\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->expects($this->once())->method('create')
            ->with($this->equalTo('ClassName'), $this->equalTo(['input']))
            ->will($this->returnValue($adapter));

        $result = $this->adapterFactory->create(['input']);
        $this->assertInstanceOf(\Magento\Framework\Search\AdapterInterface::class, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateExceptionThrown()
    {
        $this->engineResolverMock->expects($this->once())->method('getCurrentSearchEngine')
            ->will($this->returnValue('ClassName'));

        $this->objectManager->expects($this->once())->method('create')
            ->with($this->equalTo('ClassName'), $this->equalTo(['input']))
            ->will($this->returnValue('t'));

        $this->adapterFactory->create(['input']);
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateLogicException()
    {
        $this->engineResolverMock->expects($this->once())->method('getCurrentSearchEngine')
            ->will($this->returnValue('Class'));

        $this->adapterFactory->create(['input']);
    }
}
