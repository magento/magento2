<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Rate;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Tax\Model\Rate\Provider;

class SourceTest extends \PHPUnit\Framework\TestCase
{
    public function testToOptionArray()
    {
        /** @var \Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection $collection */
        $collection = Bootstrap::getObjectManager()->get(
            \Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection::class
        );

        $taxRateProvider = Bootstrap::getObjectManager()->get(Provider::class);
        $expectedResult = [];
        /** @var $taxRate \Magento\Tax\Model\Calculation\Rate */
        foreach ($collection as $taxRate) {
            $expectedResult[] = ['value' => $taxRate->getId(), 'label' => $taxRate->getCode()];
            if (count($expectedResult) >= $taxRateProvider->getPageSize()) {
                break;
            }
        }

        /** @var \Magento\Tax\Model\Rate\Source $source */
        if (empty($expectedResult)) {
            $this->fail('Preconditions failed: At least one tax rate should be available.');
        }
        $source = Bootstrap::getObjectManager()->get(\Magento\Tax\Model\Rate\Source::class);
        $this->assertEquals(
            $expectedResult,
            $source->toOptionArray(),
            'Tax rate options are invalid.'
        );
    }
}
