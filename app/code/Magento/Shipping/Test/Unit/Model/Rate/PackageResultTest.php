<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Model\Rate;

use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Rate\PackageResult;
use Magento\Shipping\Model\Rate\Result;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;

/**
 * Testing packages aware rates result.
 *
 * Unit test is suitable since CarrierResult only uses DTOs and contains all the logic it needs itself.
 */
class PackageResultTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var ErrorFactory|MockObject
     */
    private $errorFactory;

    /**
     * @var PackageResult
     */
    private $result;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->errorFactory = $this->getMockBuilder(ErrorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $errorMock = $this->getMockBuilder(Error::class)->disableOriginalConstructor()->getMock();
        $errorMock->method('getErrorMessage')->willReturn('error message');
        $this->errorFactory->method('create')->willReturn($errorMock);

        $this->result = new PackageResult($this->storeManager, $this->errorFactory);
    }

    /**
     * Test accumulating all the rates.
     */
    public function testNoRates(): void
    {
        $this->assertTrue($this->result->getError());
        $this->assertCount(1, $rates = $this->result->getAllRates());
        $this->assertInstanceOf(Error::class, $rates[0]);
        $this->assertEquals('error message', $rates[0]->getErrorMessage());
    }

    /**
     * Test composing received rates.
     */
    public function testComposing(): void
    {
        $rate1 = $this->getMockBuilder(Method::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethod', 'getPrice', 'setPrice'])
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
        $result1->expects($this->once())
            ->method('updateRatePrice')
            ->with(2)
            ->willReturnCallback(
                function () use (&$price1) {
                    $price1 = $price1 * 2;
                }
            );

        $rate2 = $this->getMockBuilder(Method::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethod', 'getPrice', 'setPrice'])
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
        $result2 = $this->getMockBuilder(Result::class)->disableOriginalConstructor()->getMock();
        $result2->method('getAllRates')->willReturn([$rate2]);
        $result2->expects($this->once())
            ->method('updateRatePrice')
            ->with(3)
            ->willReturnCallback(
                function () use (&$price2) {
                    $price2 = $price2 * 3;
                }
            );

        $this->result->appendPackageResult($result1, 2);
        $this->result->appendPackageResult($result2, 3);
        $rates = $this->result->getAllRates();
        $this->assertCount(1, $rates);
        $this->assertEquals(18, $rates[0]->getPrice());
    }

    /**
     * Case when the same results are given more than once.
     *
     */
    public function testAppendSameReference(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Same object received from carrier.');

        $rate1 = $this->getMockBuilder(Method::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethod', 'getPrice', 'setPrice'])
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

        $this->result->appendPackageResult($result1, 1);
        $this->result->appendPackageResult($result1, 2);
        $this->result->getAllRates();
    }
}
