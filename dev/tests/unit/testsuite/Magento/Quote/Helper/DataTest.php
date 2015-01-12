<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Quote\Helper;

/**
 * Tests For Quote Data checker
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Helper\Data
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Quote\Model\Quote
     */
    protected $quoteMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->helper = new \Magento\Quote\Helper\Data();

        $this->quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->setMethods(['getHasError', 'setHasError', 'addMessage', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCheckQuoteAmountExistingError()
    {
        $this->quoteMock->expects($this->once())
            ->method('getHasError')
            ->will($this->returnValue(true));

        $this->quoteMock->expects($this->never())
            ->method('setHasError');

        $this->quoteMock->expects($this->never())
            ->method('addMessage');

        $this->assertSame(
            $this->helper,
            $this->helper->checkQuoteAmount($this->quoteMock, Data::MAXIMUM_AVAILABLE_NUMBER + 1)
        );
    }

    public function testCheckQuoteAmountAmountLessThanAvailable()
    {
        $this->quoteMock->expects($this->once())
            ->method('getHasError')
            ->will($this->returnValue(false));

        $this->quoteMock->expects($this->never())
            ->method('setHasError');

        $this->quoteMock->expects($this->never())
            ->method('addMessage');

        $this->assertSame(
            $this->helper,
            $this->helper->checkQuoteAmount($this->quoteMock, Data::MAXIMUM_AVAILABLE_NUMBER - 1)
        );
    }

    public function testCheckQuoteAmountAmountGreaterThanAvailable()
    {
        $this->quoteMock->expects($this->once())
            ->method('getHasError')
            ->will($this->returnValue(false));

        $this->quoteMock->expects($this->once())
            ->method('setHasError')
            ->with(true);

        $this->quoteMock->expects($this->once())
            ->method('addMessage')
            ->with(__('This item price or quantity is not valid for checkout.'));

        $this->assertSame(
            $this->helper,
            $this->helper->checkQuoteAmount($this->quoteMock, Data::MAXIMUM_AVAILABLE_NUMBER + 1)
        );
    }
}
