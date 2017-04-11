<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Model\Directpost\Request;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Authorizenet\Model\Directpost\Request\Factory
     */
    protected $requestFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Authorizenet\Model\Directpost\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMock(\Magento\Authorizenet\Model\Directpost\Request::class, [], [], '', false);

        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class, [], [], '', false);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Authorizenet\Model\Directpost\Request::class, [])
            ->willReturn($this->requestMock);

        $this->requestFactory = $objectManager->getObject(
            \Magento\Authorizenet\Model\Directpost\Request\Factory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $this->assertSame($this->requestMock, $this->requestFactory->create());
    }
}
