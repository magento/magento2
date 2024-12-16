<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SwaggerWebapi\Test\Unit\Model\SchemaType;

use Magento\Swagger\Api\Data\SchemaTypeInterface;
use Magento\SwaggerWebapi\Model\SchemaType\Rest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RestTest extends TestCase
{
    /**
     * @var SchemaTypeInterface|MockObject
     */
    private $rest;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
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
    public static function getSchemaUrlPathProvider()
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
