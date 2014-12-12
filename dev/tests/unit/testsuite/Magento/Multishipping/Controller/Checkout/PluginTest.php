<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Multishipping\Controller\Checkout;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var Plugin
     */
    protected $object;

    protected function setUp()
    {
        $this->cartMock = $this->getMock('Magento\Checkout\Model\Cart', [], [], '', false);
        $this->quoteMock = $this->getMock(
            'Magento\Sales\Model\Quote',
            ['__wakeUp', 'setIsMultiShipping'],
            [],
            '',
            false
        );
        $this->cartMock->expects($this->once())->method('getQuote')->will($this->returnValue($this->quoteMock));
        $this->object = new Plugin($this->cartMock);
    }

    public function testExecuteTurnsOffMultishippingModeOnQuote()
    {
        $subject = $this->getMock('Magento\Checkout\Controller\Onepage\Index', [], [], '', false);
        $this->quoteMock->expects($this->once())->method('setIsMultiShipping')->with(0);
        $this->object->beforeExecute($subject);
    }
}
