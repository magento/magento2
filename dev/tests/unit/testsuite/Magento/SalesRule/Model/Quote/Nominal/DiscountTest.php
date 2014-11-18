<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
                    '__wakeup'
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
