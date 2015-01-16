<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Integration\Source;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    public function testToOptionArray()
    {
        /** @var \Magento\Integration\Model\Integration\Source\Status */
        $statusSource = new \Magento\Integration\Model\Integration\Source\Status();
        /** @var array */
        $expectedStatusArr = [
            ['value' => \Magento\Integration\Model\Integration::STATUS_INACTIVE, 'label' => __('Inactive')],
            ['value' => \Magento\Integration\Model\Integration::STATUS_ACTIVE, 'label' => __('Active')],
        ];
        $statusArr = $statusSource->toOptionArray();
        $this->assertEquals($expectedStatusArr, $statusArr, "Status source arrays don't match");
    }
}
