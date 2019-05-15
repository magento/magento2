<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Model\Rate;

use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Rate\PackageResult;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;

/**
 * Testing packages aware rates result.
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
    protected function setUp()
    {
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->errorFactory = $this->getMockBuilder(ErrorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $errorMock = $this->getMockBuilder(Error::class)->disableOriginalConstructor()->getMock();
        $errorMock->expects($this->any())->method('getErrorMessage')->willReturn('error message');
        $this->errorFactory->expects($this->any())->method('create')->willReturn($errorMock);

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
}
