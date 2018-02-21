<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Swagger\Api\SchemaTypeInterface;
use Magento\Swagger\Block\SchemaTypes;

class SchemaTypesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SchemaTypeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $defaultType;

    /**
     * @var SchemaTypes
     */
    private $schemaTypes;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->defaultType = $this->getMockBuilder(SchemaTypeInterface::class)
            ->getMock();

        $this->schemaTypes = (new ObjectManager($this))->getObject(
            SchemaTypes::class,
            [
                'types' => [
                    $this->defaultType,
                    $this->getMockBuilder(SchemaTypeInterface::class)->getMock()
                ]
            ]
        );
    }

    /**
     * @covers \Magento\Swagger\Block\SchemaTypes::getTypes()
     */
    public function testGetTypes()
    {
        $this->assertCount(2, $this->schemaTypes->getTypes());
        $this->assertContains($this->defaultType, $this->schemaTypes->getTypes());
    }

    /**
     * Test that the first type supplied to SchemaTypes is the default
     *
     * @covers \Magento\Swagger\Block\SchemaTypes::getDefault()
     */
    public function testGetDefaultType()
    {
        $this->assertEquals($this->defaultType, $this->schemaTypes->getDefault());
    }
}
