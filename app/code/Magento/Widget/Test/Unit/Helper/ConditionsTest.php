<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Unit\Helper;

/**
 * Class ConditionsTest
 */
class ConditionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditions;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->serializer = $this->getMock(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->serializer->method('serialize')->willReturnCallback(function ($value) {
            return json_encode($value);
        });
        $this->serializer->method('unserialize')->willReturnCallback(function ($value) {
            return json_decode($value, true);
        });
        $this->conditions = new \Magento\Widget\Helper\Conditions(
            $this->serializer
        );
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
