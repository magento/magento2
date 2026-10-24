<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Integration\Source;

use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Integration\Source\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    public function testToOptionArray()
    {
        /** @var Status */
        $statusSource = new Status();
        /** @var array */
        $expectedStatusArr = [
            ['value' => Integration::STATUS_INACTIVE, 'label' => __('Inactive')],
            ['value' => Integration::STATUS_ACTIVE, 'label' => __('Active')],
            ['value' => Integration::STATUS_RECREATED, 'label' => __('Reset')],
        ];
        $statusArr = $statusSource->toOptionArray();
        $this->assertEquals($expectedStatusArr, $statusArr, "Status source arrays don't match");
    }
}
