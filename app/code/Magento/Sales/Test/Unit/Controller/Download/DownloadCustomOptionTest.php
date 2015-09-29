<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class DownloadCustomOptionTest
 * @package Magento\Sales\Controller\Adminhtml\Order
 */
class DownloadCustomOptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Option ID Test Value
     */
    const OPTION_ID = '123456';

    /**
     * Option Code Test Value
     */
    const OPTION_CODE = 'option_123456';

    /**
     * Option Product ID Value
     */
    const OPTION_PRODUCT_ID = 'option_test_product_id';

    /**
     * Option Type Value
     */
    const OPTION_TYPE = 'file';

    /**
     * Option Value Test Value
     */
    const OPTION_VALUE = 'option_test_value';

    /**
     * @var \Magento\Quote\Model\Quote\Item\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemOptionMock;

    /**
     * @var \Magento\Catalog\Model\Product\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productOptionMock;

    /**
     * @var \Magento\Framework\Unserialize\Unserialize|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $unserializeMock;

    /**
     * @var \Magento\Framework\Controller\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardMock;

    /**
     * @var \Magento\Sales\Model\Download|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $downloadMock;

    /**
     * @var \Magento\Sales\Controller\Download\DownloadCustomOption
     */
    protected $object;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $resultForwardFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\Result\ForwardFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultForwardMock = $this->getMockBuilder('\Magento\Framework\Controller\Result\Forward')
            ->disableOriginalConstructor()
            ->setMethods(['forward'])
            ->getMock();
        $resultForwardFactoryMock->expects($this->any())->method('create')->willReturn($resultForwardMock);

        $this->downloadMock = $this->getMockBuilder('Magento\Sales\Model\Download')
            ->disableOriginalConstructor()
            ->setMethods(['downloadFile'])
            ->getMock();

        $this->unserializeMock = $this->getMockBuilder('Magento\Framework\Unserialize\Unserialize')
            ->disableOriginalConstructor()
            ->setMethods(['downloadFile'])
            ->getMock();

        $requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();
        $requestMock->expects($this->once())->method('getParam')->willReturn(self::OPTION_ID);

        $this->itemOptionMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item\Option')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'getCode', 'getProductId', 'getValue'])
            ->getMock();
        $this->itemOptionMock->expects($this->once())->method('load')->willReturnSelf();

        $this->productOptionMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Option')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'getProductId', 'getType'])
            ->getMock();
        $this->productOptionMock->expects($this->once())->method('load')->willReturnSelf();

        $objectManagerMock = $this->getMockBuilder('Magento\Sales\Model\Download')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $objectManagerMock->expects($this->any())->method('create')
            ->will(
                $this->returnValueMap(
                    [
                        ['Magento\Quote\Model\Quote\Item\Option', $this->itemOptionMock],
                        ['Magento\Catalog\Model\Product\Option', $this->productOptionMock],
                    ]
                )
            );

        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRequest',
                    'getObjectManager',
                ]
            )
            ->getMock();
        $contextMock->expects($this->once())->method('getObjectManager')->willReturn($objectManagerMock);
        $contextMock->expects($this->once())->method('getRequest')->willReturn($requestMock);

        $this->object = $objectManagerHelper->getObject(
            'Magento\Sales\Controller\Download\DownloadCustomOption',
            [
                'context'              => $contextMock,
                'resultForwardFactory' => $resultForwardFactoryMock,
                'download'             => $this->downloadMock,
                'unserialize'          => $this->unserializeMock
            ]
        );
    }

    /**
     * @param array $itemOptionValues
     * @param array $productOptionValues
     * @param bool $noRouteOccurs
     * @dataProvider executeDataProvider
     */
    public function testExecute($itemOptionValues, $productOptionValues, $noRouteOccurs)
    {
        $this->itemOptionMock->expects($this->once())
            ->method('getId')
            ->willReturn($itemOptionValues[self::OPTION_ID]);
        $this->itemOptionMock->expects($this->once())
            ->method('getCode')
            ->willReturn($itemOptionValues[self::OPTION_CODE]);
        $this->itemOptionMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($itemOptionValues[self::OPTION_PRODUCT_ID]);
        $this->itemOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($itemOptionValues[self::OPTION_VALUE]);

        $this->productOptionMock->expects($this->once())
            ->method('getId')
            ->willReturn($productOptionValues[self::OPTION_ID]);
        $this->productOptionMock->expects($this->once())
            ->method('getId')
            ->willReturn($productOptionValues[self::OPTION_PRODUCT_ID]);
        $this->productOptionMock->expects($this->once())
            ->method('getId')
            ->willReturn($productOptionValues[self::OPTION_TYPE]);

        if ($noRouteOccurs) {
            $this->resultForwardMock->expects($this->once())->method('forward')->with('noroute')->willReturn(true);
        } else {
            $this->unserializeMock->expects($this->once())
                ->method('unserialize')
                ->with($itemOptionValues[self::OPTION_VALUE])
                ->willReturn($itemOptionValues[self::OPTION_VALUE] . 'unserialized');
            $this->downloadMock->expects($this->once())
                ->method('downloadFile')
                ->with($itemOptionValues[self::OPTION_VALUE] . 'unserialized')
                ->willReturn(true);
        }

        $this->object->execute();
    }

    public function executeDataProvider() {
        return [
            [
                [
                    self::OPTION_ID => self::OPTION_ID,
                    self::OPTION_CODE => self::OPTION_CODE,
                    self::OPTION_PRODUCT_ID => self::OPTION_PRODUCT_ID,
                    self::OPTION_VALUE => self::OPTION_VALUE
                ],
                [
                    self::OPTION_ID => self::OPTION_ID,
                    self::OPTION_PRODUCT_ID => self::OPTION_PRODUCT_ID,
                    self::OPTION_TYPE => self::OPTION_TYPE,
                ],
                false
            ],

        ];
    }
}
