<?php
/**
 * Created by PhpStorm.
 * User: jpolak
 * Date: 9/20/17
 * Time: 10:38 AM
 */

namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\OneTouchOrdering\Model\CheapestShippingRateChooser;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CheapestShippingRateChooserTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CheapestShippingRateChooser
     */
    private $shippingRateChooser;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Quote
     */
    private $quote;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAddress;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->quote = $this->createMock(Quote::class);
        $this->shippingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['setCollectShippingRates', 'collectShippingRates', 'getAllShippingRates', 'setShippingMethod']
            )->getMock();
        $this->shippingRateChooser = $objectManager->getObject(CheapestShippingRateChooser::class);
    }

    public function testChoose()
    {
        $shippingRates = [
            ['code' => 'expensive_rate', 'price' => 100],
            ['code' => 'cheap_rate', 'price' => 10]
        ];

        $this->quote
            ->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddress);
        $this->shippingAddress
            ->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();
        $this->shippingAddress
            ->expects($this->once())
            ->method('collectShippingRates')
            ->willReturnSelf();
        $this->shippingAddress
            ->expects($this->once())
            ->method('getAllShippingRates')
            ->willReturn($shippingRates);
        $this->shippingAddress
            ->expects($this->once())
            ->method('setShippingMethod')
            ->with('cheap_rate');
        $this->shippingRateChooser->choose($this->quote);
    }

    public function testChooseNoCC()
    {
        $this->quote
            ->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddress);
        $this->shippingAddress
            ->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true)->willReturnSelf();
        $this->shippingAddress->expects($this->once())->method('collectShippingRates')->willReturnSelf();
        $this->shippingAddress->expects($this->once())->method('getAllShippingRates')->willReturn([]);
        $this->expectException(LocalizedException::class);
        $this->shippingRateChooser->choose($this->quote);
    }
}
