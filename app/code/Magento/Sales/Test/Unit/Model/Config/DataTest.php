<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Config;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_cacheMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->_readerMock = $this->getMockBuilder(
            \Magento\Sales\Model\Config\Reader::class
        )->disableOriginalConstructor()->getMock();
        $this->_cacheMock = $this->getMockBuilder(
            \Magento\Framework\App\Cache\Type\Config::class
        )->disableOriginalConstructor()->getMock();
        $this->serializerMock = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $this->mockObjectManager(
            [\Magento\Framework\Serialize\SerializerInterface::class => $this->serializerMock]
        );
    }

    protected function tearDown()
    {
        $reflectionProperty = new \ReflectionProperty(\Magento\Framework\App\ObjectManager::class, '_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null);
    }

    /**
     * Mock application object manager to return configured dependencies.
     *
     * @param array $dependencies
     * @return void
     */
    private function mockObjectManager($dependencies)
    {
        $dependencyMap = [];
        foreach ($dependencies as $type => $instance) {
            $dependencyMap[] = [$type, $instance];
        }
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($dependencyMap));
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
    }

    public function testGet()
    {
        $expected = ['someData' => ['someValue', 'someKey' => 'someValue']];
        $this->_cacheMock->expects($this->once())
            ->method('load');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($expected);

        $configData = new \Magento\Sales\Model\Config\Data($this->_readerMock, $this->_cacheMock);

        $this->assertEquals($expected, $configData->get());
    }
}
