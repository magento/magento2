<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Rate;

use Magento\TestFramework\Helper\Bootstrap;

class SourceTest extends \PHPUnit_Framework_TestCase
{
    public function testToOptionArray()
    {
        /** @var \Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection $collection */
        $collection = Bootstrap::getObjectManager()->get('Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection');
        $expectedResult = [];
        /** @var $taxRate \Magento\Tax\Model\Calculation\Rate */
        foreach ($collection as $taxRate) {
            $expectedResult[] = ['value' => $taxRate->getId(), 'label' => $taxRate->getCode()];
        }
        /** @var \Magento\Tax\Model\Rate\Source $source */
        if (empty($expectedResult)) {
            $this->fail('Preconditions failed: At least one tax rate should be available.');
        }
        $source = Bootstrap::getObjectManager()->get('Magento\Tax\Model\Rate\Source');
        $this->assertEquals(
            $expectedResult,
            $source->toOptionArray(),
            'Tax rate options are invalid.'
        );
    }
}
