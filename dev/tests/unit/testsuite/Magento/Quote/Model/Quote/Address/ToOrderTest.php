<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Quote\Model\Quote\Address;

use Magento\TestFramework\Helper\ObjectManager;

/**
 * Tests Address convert to order
 */
class ToOrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Object\Copy | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectCopyMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderDataBuilder | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderDataBuilderMock;

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
        $this->orderDataBuilderMock = $this->getMock('Magento\Sales\Api\Data\OrderDataBuilder', [], [], '', false);
        $this->objectCopyMock = $this->getMock('Magento\Framework\Object\Copy', [], [], '', false);
        $this->orderInterfaceMock = $this->getMock('Magento\Sales\Api\Data\OrderInterface', [], [], '', false);
        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(
            'Magento\Quote\Model\Quote\Address\ToOrder',
            [
                'orderBuilder' => $this->orderDataBuilderMock,
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
            'to_order',
            $object
        )->willReturn($orderData);
        $this->orderDataBuilderMock->expects($this->once())->method('populateWithArray')
            ->with(['test' => 'beer'])
            ->willReturnSelf();
        $this->orderDataBuilderMock->expects($this->once())->method('create')->willReturn($this->orderInterfaceMock);
        $this->assertSame($this->orderInterfaceMock, $this->converter->convert($object, $data));
    }
}
