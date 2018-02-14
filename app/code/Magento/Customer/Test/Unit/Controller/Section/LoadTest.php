<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Section;

use Magento\Customer\Controller\Section\Load;
use Magento\Customer\CustomerData\Section\Identifier;
use Magento\Customer\CustomerData\SectionPoolInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit_Framework_MockObject_MockObject;

class LoadTest extends \PHPUnit_Framework_TestCase
{
    /** @var Load|PHPUnit_Framework_MockObject_MockObject */
    private $actionMock;
    /** @var Json|PHPUnit_Framework_MockObject_MockObject $jsonMock */
    private $jsonMock;

    public function setUp()
    {
        $this->jsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var JsonFactory|PHPUnit_Framework_MockObject_MockObject */
        $jsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $jsonFactoryMock->expects($this->once())->method('create')->willReturn($this->jsonMock);

        $this->actionMock = $this->getMockBuilder(Load::class)
            ->setConstructorArgs(
                [
                    $this->mockContext(),
                    $jsonFactoryMock,
                    $this->getMockBuilder(Identifier::class)->disableOriginalConstructor()->getMock(),
                    $this->getMockBuilder(SectionPoolInterface::class)->disableOriginalConstructor()->getMock()
                ]
            )
            ->setMethods(["getRequest"])
            ->getMock();
    }

    /**
     * Test escaped response
     *
     * @dataProvider provideMessages
     *
     * @param string $message
     * @param string $expectedMessage
     */
    public function testEscapedResponse($message, $expectedMessage)
    {
        $this->jsonMock->expects($this->once())
            ->method('setData')
            ->with(['message' => $expectedMessage])
            ->willReturnSelf();

        /** @var RequestInterface|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->once())->method("getParam")->willThrowException(new \Exception($message));

        $this->actionMock->expects($this->once())->method('getRequest')->willReturn($request);
        $this->actionMock->execute();
    }

    /**
     * @return array
     */
    public function provideMessages()
    {
        return [
            ["test", "test"],
            ["test<script>", "test&lt;script&gt;"],
            ["test<script>alert()</script>", "test&lt;script&gt;alert()&lt;/script&gt;"],
        ];
    }

    /**
     * @return Context|PHPUnit_Framework_MockObject_MockObject
     */
    public function mockContext()
    {
        /** @var Context|PHPUnit_Framework_MockObject_MockObject $contextMock */
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Escaper::class)
            ->willReturn(new Escaper());

        $contextMock->expects($this->once())->method('getObjectManager')->willReturn($objectManagerMock);
        return $contextMock;
    }
}
