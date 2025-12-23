<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Checks;

use Magento\Payment\Model\Checks\Composite;
use Magento\Payment\Model\Checks\SpecificationInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CompositeTest extends TestCase
{
    /**
     * @param bool $expectation
     */
    #[DataProvider('paymentMethodDataProvider')]
    public function testIsApplicable($expectation)
    {
        $quote = $this->createMock(Quote::class);
        $paymentMethod = $this->createMock(MethodInterface::class);

        $specification = $this->createMock(SpecificationInterface::class);
        $specification->expects($this->once())->method('isApplicable')->with($paymentMethod, $quote)->willReturn(
            $expectation
        );
        $model = new Composite([$specification]);
        $this->assertEquals($expectation, $model->isApplicable($paymentMethod, $quote));
    }

    /**
     * @return array
     */
    public static function paymentMethodDataProvider()
    {
        return [[true], [false]];
    }
}
