<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Model\Response;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Authorizenet\Model\Response\Factory
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Authorizenet\Model\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->responseMock = $this->getMock('Magento\Authorizenet\Model\Response', [], [], '', false);

        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Authorizenet\Model\Response', [])
            ->willReturn($this->responseMock);

        $this->responseFactory = $objectManager->getObject(
            'Magento\Authorizenet\Model\Response\Factory',
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $this->assertSame($this->responseMock, $this->responseFactory->create());
    }
}
