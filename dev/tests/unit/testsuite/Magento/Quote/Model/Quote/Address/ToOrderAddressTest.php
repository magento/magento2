<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Quote\Model\Quote\Address;

use Magento\TestFramework\Helper\ObjectManager;

/**
 * Tests Address convert to order
 */
class ToOrderAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Object\Copy | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectCopyMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderAddressDataBuilder | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAddressBuilderMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderInterfaceMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrder
     */
    protected $converter;

    protected function setUp()
    {
        $this->orderAddressBuilderMock = $this->getMock(
            'Magento\Sales\Api\Data\OrderAddressDataBuilder',
            [],
            [],
            '',
            false
        );
        $this->objectCopyMock = $this->getMock('Magento\Framework\Object\Copy', [], [], '', false);
        $this->orderInterfaceMock = $this->getMock('Magento\Sales\Api\Data\OrderInterface', [], [], '', false);
        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(
            'Magento\Quote\Model\Quote\Address\ToOrderAddress',
            [
                'orderAddressBuilder' => $this->orderAddressBuilderMock,
                'objectCopyService' => $this->objectCopyMock
            ]
        );
    }

    public function testConvert()
    {
        $orderData = ['test' => 'test1'];
        $data = ['test' => 'beer'];
        /**
         * @var \Magento\Quote\Model\Quote\Address $object
         */
        $object = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);
        $this->objectCopyMock->expects($this->once())->method('getDataFromFieldset')->with(
            'quote_convert_address',
            'to_order_address',
            $object
        )->willReturn($orderData);
        $this->orderAddressBuilderMock->expects($this->once())->method('populateWithArray')
            ->with(['test' => 'beer'])
            ->willReturnSelf();
        $this->orderAddressBuilderMock->expects($this->once())->method('create')->willReturn($this->orderInterfaceMock);
        $this->assertSame($this->orderInterfaceMock, $this->converter->convert($object, $data));
    }
}
