<?php
/**
 * Created by PhpStorm.
 * User: jpolak
 * Date: 9/20/17
 * Time: 10:38 AM
 */

namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\OneTouchOrdering\Model\ShippingRateChooserRuleInterface;
use Magento\OneTouchOrdering\Model\ShippingRateChooser;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ShippingRateChooserTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Quote
     */
    private $quote;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAddress;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingRateChooserRule;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ShippingRateChooser
     */
    private $shippingRateChooser;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->quote = $this->createMock(Quote::class);
        $this->shippingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['setCollectShippingRates', 'collectShippingRates', 'getAllShippingRates', 'setShippingMethod']
            )->getMock();
        $this->shippingRateChooserRule = $this->createMock(ShippingRateChooserRuleInterface::class);
        $this->shippingRateChooser = $objectManager->getObject(ShippingRateChooser::class,
            ['shippingRateChooserRule' => $this->shippingRateChooserRule]
        );
    }

    public function testChoose()
    {
        $shippingRates = [
            ['code' => 'expensive_rate', 'price' => 100],
            ['code' => 'cheap_rate', 'price' => 10]
        ];
        $chosenCode = 'cheap_rate';

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
        $this->shippingRateChooserRule
            ->expects($this->once())
            ->method('choose')
            ->with($shippingRates)
            ->willReturn($chosenCode);
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
