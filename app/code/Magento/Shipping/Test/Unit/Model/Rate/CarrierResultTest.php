<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Model\Rate;

use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Rate\CarrierResult;
use Magento\Shipping\Model\Rate\Result;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Testing carrier result.
 *
 * Unit test is suitable since CarrierResult only uses DTOs and contains all the logic it needs itself.
 */
class CarrierResultTest extends TestCase
{
    /**
     * @var CarrierResult|MockObject
     */
    private $result;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        /** @var MockObject|StoreManagerInterface $storeManager */
        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->result = new CarrierResult($storeManager);
    }

    /**
     * Test composing all the rates.
     */
    public function testComposing(): void
    {
        $rate1 = $this->getMockBuilder(Method::class)
            ->disableOriginalConstructor()
            ->addMethods(['getMethod', 'getPrice'])
            ->onlyMethods(['setPrice'])
            ->getMock();
        $price1 = 3;
        $rate1->method('getMethod')->willReturn('method');
        $rate1->method('getPrice')->willReturnReference($price1);
        $rate1->method('setPrice')
            ->willReturnCallback(
                function ($price) use (&$price1) {
                    $price1 = $price;
                }
            );
        /** @var Result|MockObject $result1 */
        $result1 = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result1->method('getAllRates')->willReturn([$rate1]);
        $result1->method('getError')->willReturn(false);

        $rate2 = $this->getMockBuilder(Method::class)
            ->disableOriginalConstructor()
            ->addMethods(['getMethod', 'getPrice'])
            ->onlyMethods(['setPrice'])
            ->getMock();
        $price2 = 4;
        $rate2->method('getMethod')->willReturn('method');
        $rate2->method('getPrice')->willReturnReference($price2);
        $rate2->method('setPrice')
            ->willReturnCallback(
                function ($price) use (&$price2) {
                    $price2 = $price;
                }
            );
        /** @var Result|MockObject $result2 */
        $result2 = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result2->method('getAllRates')->willReturn([$rate2]);
        $result2->method('getError')->willReturn(false);

        $rate3 = $this->getMockBuilder(Error::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Result|MockObject $result3 */
        $result3 = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result3->method('getAllRates')->willReturn([$rate3]);
        $result3->method('getError')->willReturn(true);

        $rate4 = $this->getMockBuilder(Error::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Result|MockObject $result4 */
        $result4 = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result4->method('getAllRates')->willReturn([$rate4]);
        $result4->method('getError')->willReturn(true);

        //Composing
        $this->result->appendResult($result1, false);
        $this->result->appendResult($result2, false);
        $this->result->appendResult($result3, false);
        $this->result->appendResult($result4, true);
        $rates = $this->result->getAllRates();
        $this->assertCount(3, $rates);
        $this->assertTrue($this->result->getError());
    }
}
