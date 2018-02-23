<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SwaggerWebapi\Test\Unit\Model\SchemaType;

use Magento\Swagger\Api\SchemaTypeInterface;
use Magento\SwaggerWebapi\Model\SchemaType\Async;

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
        // @todo: implement constant once merged with other bulk-api changes
        $this->async = new Async('async');
    }

    /**
     * @covers \Magento\SwaggerWebapi\Model\SchemaType\Async::getCode()
     */
    public function testGetCode()
    {
        // @todo: implement constant once merged with other bulk-api changes
        $this->assertEquals('async', $this->async->getCode());
    }

    /**
     * @covers \Magento\SwaggerWebapi\Model\SchemaType\Async::getSchemaUrlPathProvider
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
                '/async/all/schema?services=all'
            ],
            [
                'test',
                '/async/test/schema?services=all'
            ]
        ];
    }
}
