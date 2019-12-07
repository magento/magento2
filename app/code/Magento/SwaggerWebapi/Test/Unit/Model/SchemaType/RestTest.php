<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SwaggerWebapi\Test\Unit\Model\SchemaType;

use Magento\Swagger\Api\Data\SchemaTypeInterface;
use Magento\SwaggerWebapi\Model\SchemaType\Rest;

class RestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SchemaTypeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rest;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->rest = new Rest('rest');
    }

    /**
     * @covers \Magento\SwaggerWebapi\Model\SchemaType\Rest::getSchemaUrlPath
     *
     * @param $expected
     * @param null|string $store
     *
     * @dataProvider getSchemaUrlPathProvider
     */
    public function testGetSchemaUrlPath($expected, $store = null)
    {
        $this->assertEquals($expected, $this->rest->getSchemaUrlPath($store));
    }

    /**
     * @covers \Magento\SwaggerWebapi\Model\SchemaType\Rest::getCode()
     */
    public function testGetCode()
    {
        $this->assertEquals('rest', $this->rest->getCode());
    }

    /**
     * @return array
     */
    public function getSchemaUrlPathProvider()
    {
        return [
            [
                '/rest/all/schema?services=all',
                null
            ],
            [
                '/rest/test/schema?services=all',
                'test'
            ]
        ];
    }
}
