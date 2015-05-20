<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Model\Oauth;

/**
 * Unit test for \Magento\Integration\Model\Oauth\NonceFactory
 */
class NonceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Model\Oauth\NonceFactory
     */
    protected $nonceFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->nonceFactory = new \Magento\Integration\Model\Oauth\NonceFactory($this->objectManagerMock);
    }

    public function testCreate()
    {
        $nonceMock = $this->getMockBuilder('Magento\Integration\Model\Oauth\Nonce')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Integration\Model\Oauth\Nonce', [])
            ->will($this->returnValue($nonceMock));

        $this->assertEquals($nonceMock, $this->nonceFactory->create());
    }
}
