<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller\Index;

class DownloadCustomOptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Controller\Index\DownloadCustomOption
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileResponseFactoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonMock;

    protected function setUp()
    {
        $this->fileResponseFactoryMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http\FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'get', 'configure'])
            ->getMock();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $this->model = new \Magento\Wishlist\Controller\Index\DownloadCustomOption(
            $this->contextMock,
            $this->fileResponseFactoryMock,
            $this->jsonMock
        );
    }

    public function testExecute()
    {
        $data = [
            'number' => 42,
            'string' => 'string_value',
            'boolean' => true,
            'collection' => [1, 2, 3],
            'secret_key' => 999
        ];
        $serialized_data = json_encode($data);

        $optionMock = $this->getMockBuilder(\Magento\Wishlist\Model\Item\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $optionMock->expects($this->any())
            ->method('getId')
            ->willReturn(true);
        $optionMock->expects($this->any())
            ->method('getProductId')
            ->willReturn('some_value');
        $optionMock->expects($this->any())
            ->method('getValue')
            ->willReturn($serialized_data);

        $productOptionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productOptionMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $productOptionMock->expects($this->any())
            ->method('getId')
            ->willReturn(true);
        $productOptionMock->expects($this->any())
            ->method('getProductId')
            ->willReturn('some_value');
        $productOptionMock->expects($this->any())
            ->method('getType')
            ->willReturn('file');

        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnMap(
                [
                    [\Magento\Wishlist\Model\Item\Option::class, [], $optionMock],
                    [\Magento\Catalog\Model\Product\Option::class, [], $productOptionMock]
                ]
            );

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturn(1);

        $this->jsonMock->expects($this->once())
            ->method('unserialize')
            ->willReturnCallback(function ($value) {
                return json_decode($value, true);
            });

        $this->assertEquals(null, $this->model->execute());
    }
}
