<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Checks;

use Magento\Payment\Model\Checks\CanUseInternal;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CanUseInternalTest extends TestCase
{
    /**
     * @var CanUseInternal
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new CanUseInternal();
    }

    /**
     * @param bool $expectation
     */
    #[DataProvider('paymentMethodDataProvider')]
    public function testIsApplicable($expectation)
    {
        $quote = $this->createMock(Quote::class);
        $paymentMethod = $this->createMock(MethodInterface::class);
        $paymentMethod->expects($this->once())->method('canUseInternal')->willReturn(
            $expectation
        );
        $this->assertEquals($expectation, $this->_model->isApplicable($paymentMethod, $quote));
    }

    /**
     * @return array
     */
    public static function paymentMethodDataProvider()
    {
        return [[true], [false]];
    }
}
