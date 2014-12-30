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
     * @var \Magento\Sales\Api\Data\OrderDataBuilder | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderDataBuilderMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrder
     */
    protected $converter;

    protected function setUp()
    {
        $this->orderDataBuilderMock = $this->getMock('Magento\Sales\Api\Data\OrderDataBuilder', [], [], '', false);
        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(
            'Magento\Quote\Model\Quote\Address\ToOrder',
            ['orderBuilder' => $this->orderDataBuilderMock]
        );
    }

    public function testConvert()
    {
       $this->markTestIncomplete('MAGETWO-32107');
    }
}
