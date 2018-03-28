<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SwaggerWebapiAsync\Test\Unit\Model\SchemaType;

use Magento\Swagger\Api\Data\SchemaTypeInterface;
use Magento\SwaggerWebapiAsync\Model\SchemaType\Async;

class AsyncTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SchemaTypeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $async;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->async = new Async('async');
    }

    /**
     * @covers \Magento\SwaggerWebapiAsync\Model\SchemaType\Async::getCode()
     */
    public function testGetCode()
    {
        $this->assertEquals('async', $this->async->getCode());
    }

    /**
     * @covers \Magento\SwaggerWebapiAsync\Model\SchemaType\Async::getSchemaUrlPathProvider
     *
     * @param null|string $store
     * @param $expected
     *
     * @dataProvider getSchemaUrlPathProvider
     */
    public function testGetSchemaUrlPath($store = null, $expected)
    {
        $this->assertEquals($expected, $this->async->getSchemaUrlPath($store));
    }

    /**
     * @return array
     */
    public function getSchemaUrlPathProvider()
    {
        return [
            [
                null,
                '/rest/all/async/schema?services=all'
            ],
            [
                'test',
                '/rest/test/async/schema?services=all'
            ]
        ];
    }
}
