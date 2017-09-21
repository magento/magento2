<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Fedex\Model\Source;

class UnitofmeasureTest extends \PHPUnit\Framework\TestCase
{
    public function testToOptionArray()
    {
        /** @var $model \Magento\Fedex\Model\Source\Unitofmeasure */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Fedex\Model\Source\Unitofmeasure::class
        );
        $result = $model->toOptionArray();
        $this->assertCount(2, $result);
    }
}
