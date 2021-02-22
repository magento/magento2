<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Checks;

use \Magento\Payment\Model\Checks\Composite;

class CompositeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider paymentMethodDataProvider
     * @param bool $expectation
     */
    public function testIsApplicable($expectation)
    {
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $paymentMethod = $this->getMockBuilder(
            \Magento\Payment\Model\MethodInterface::class
        )->disableOriginalConstructor()->setMethods([])->getMock();

        $specification = $this->getMockBuilder(
            \Magento\Payment\Model\Checks\SpecificationInterface::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $specification->expects($this->once())->method('isApplicable')->with($paymentMethod, $quote)->willReturn(
            $expectation
        );
        $model = new Composite([$specification]);
        $this->assertEquals($expectation, $model->isApplicable($paymentMethod, $quote));
    }

    /**
     * @return array
     */
    public function paymentMethodDataProvider()
    {
        return [[true], [false]];
    }
}
