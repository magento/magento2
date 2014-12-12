<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\SalesRule\Model\Quote\Nominal;

use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class DiscountTest
 */
class DiscountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Quote\Nominal\Discount
     */
    protected $discount;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Store\Model\StoreManager
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\SalesRule\Model\Validator
     */
    protected $validatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event\Manager
     */
    protected $eventManagerMock;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->validatorMock = $this->getMockBuilder('Magento\SalesRule\Model\Validator')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'canApplyRules',
                    'reset',
                    'init',
                    'initTotals',
                    'sortItemsByPriority',
                    'setSkipActionsValidation',
                    'process',
                    'processShippingAmount',
                    'canApplyDiscount',
                    '__wakeup',
                ]
            )
            ->getMock();

        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\SalesRule\Model\Quote\Nominal\Discount $discount */
        $this->discount = $this->objectManager->getObject(
            'Magento\SalesRule\Model\Quote\Nominal\Discount',
            [
                'storeManager' => $this->storeManagerMock,
                'validator'    => $this->validatorMock,
                'eventManager' => $this->eventManagerMock
            ]
        );
    }

    public function testFetch()
    {
        $addressMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInternalType('array', $this->discount->fetch($addressMock));
    }

    public function testGetNominalAddressItems()
    {
        $item = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();

        $this->validatorMock->expects($this->once())
            ->method('sortItemsByPriority')
            ->willReturnArgument(0);

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup'])
            ->getMock();

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', 'getAllNominalItems', 'getShippingAmount', '__wakeup'])
            ->getMock();

        $addressMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $addressMock->expects($this->once())
            ->method('getAllNominalItems')
            ->willReturn([$item]);

        $addressMock->expects($this->once())
            ->method('getShippingAmount')
            ->willReturn(true);

        $this->assertInstanceOf(
            'Magento\SalesRule\Model\Quote\Discount',
            $this->discount->collect($addressMock)
        );
    }
}
