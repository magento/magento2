<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Unserialize\SecureUnserializer;

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
     * @var SecureUnserializer
     */
    private $unserializerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->unserializerMock = $this->getMockBuilder(SecureUnserializer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->conditions = $objectManagerHelper->getObject(
            \Magento\Widget\Helper\Conditions::class,
            [
                'unserializer' => $this->unserializerMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testEncodeDecode()
    {
        $value = [
            '1' => [
                "type" => "Magento\\CatalogWidget\\Model\\Rule\\Condition\\Combine",
                "aggregator" => "all",
                "value" => "1",
                "new_child" => "",
            ],
            '1--1' => [
                "type" => "Magento\\CatalogWidget\\Model\\Rule\\Condition\\Product",
                "attribute" => "attribute_set_id",
                "value" => "4",
                "operator" => "==",
            ],
            '1--2' => [
                "type" => "Magento\\CatalogWidget\\Model\\Rule\\Condition\\Product",
                "attribute" => "category_ids",
                "value" => "2",
                "operator" => "==",
            ],
        ];

        $this->unserializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($value);

        $encoded = $this->conditions->encode($value);
        $this->assertEquals($value, $this->conditions->decode($encoded));
    }
}
