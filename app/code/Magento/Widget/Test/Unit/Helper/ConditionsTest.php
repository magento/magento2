<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ConditionsTest
 */
class ConditionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditions;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->conditions = $objectManagerHelper->getObject(\Magento\Widget\Helper\Conditions::class);
    }

    public function testEncodeDecode()
    {
        $value = [
            '1' => [
                "type" => \Magento\CatalogWidget\Model\Rule\Condition\Combine::class,
                "aggregator" => "all",
                "value" => "1",
                "new_child" => "",
            ],
            '1--1' => [
                "type" => \Magento\CatalogWidget\Model\Rule\Condition\Product::class,
                "attribute" => "attribute_set_id",
                "value" => "4",
                "operator" => "==",
            ],
            '1--2' => [
                "type" => \Magento\CatalogWidget\Model\Rule\Condition\Product::class,
                "attribute" => "category_ids",
                "value" => "2",
                "operator" => "==",
            ],
        ];
        $encoded = $this->conditions->encode($value);
        $this->assertEquals($value, $this->conditions->decode($encoded));
    }
}
