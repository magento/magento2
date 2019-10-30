<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model\Rule\Action;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\SimpleActionOptionsProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers Magento\SalesRule\Model\Rule\Action\SimpleActionOptionsProvider
 */
class SimpleActionOptionsProviderTest extends TestCase
{
    /**
     * @var SimpleActionOptionsProvider|MockObject
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->model = $objectManager->getObject(SimpleActionOptionsProvider::class);
    }

    public function testToOptionArray()
    {
        $expected = [
            ['label' => __('Percent of product price discount'), 'value' =>  Rule::BY_PERCENT_ACTION],
            ['label' => __('Fixed amount discount'), 'value' => Rule::BY_FIXED_ACTION],
            ['label' => __('Fixed amount discount for whole cart'), 'value' => Rule::CART_FIXED_ACTION],
            ['label' => __('Buy X get Y free (discount amount is Y)'), 'value' => Rule::BUY_X_GET_Y_ACTION]
        ];

        $this->assertEquals($expected, $this->model->toOptionArray());
    }
}
