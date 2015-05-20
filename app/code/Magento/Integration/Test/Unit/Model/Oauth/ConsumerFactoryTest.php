<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Model\Oauth;

/**
 * Unit test for \Magento\Integration\Model\Oauth\ConsumerFactory
 */
class ConsumerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Model\Oauth\ConsumerFactory
     */
    protected $consumerFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->consumerFactory = new \Magento\Integration\Model\Oauth\ConsumerFactory($this->objectManagerMock);
    }

    public function testCreate()
    {
        $consumerMock = $this->getMockBuilder('Magento\Integration\Model\Oauth\Consumer')
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $consumerMock->expects($this->once())->method('setData')->with([])->willReturnSelf();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Integration\Model\Oauth\Consumer', [])
            ->will($this->returnValue($consumerMock));

        $this->assertEquals($consumerMock, $this->consumerFactory->create());
    }
}
