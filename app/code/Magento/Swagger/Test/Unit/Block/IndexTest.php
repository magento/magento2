<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Swagger\Test\Unit\Block;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Swagger\Api\Data\SchemaTypeInterface;
use Magento\Swagger\Block\Index;
use Magento\SwaggerWebapi\Model\SchemaType\Rest;

class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SchemaTypeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $schemaTypeMock;

    /**
     * @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var Index
     */
    private $index;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)->getMock();
        $this->schemaTypeMock = $this->getMockBuilder(SchemaTypeInterface::class)->getMock();

        $this->index = (new ObjectManager($this))->getObject(
            Index::class,
            [
                'context' => (new ObjectManager($this))->getObject(
                    Context::class,
                    [
                        'request' => $this->requestMock,
                    ]
                ),
                'data' => [
                    'schema_types' => [
                        'test' => $this->schemaTypeMock
                    ]
                ]
            ]
        );
    }

    /**
     * Test that the passed URL parameter is used when it is a valid schema type.
     *
     * @covers \Magento\Swagger\Block\Index::getSchemaUrl()
     */
    public function testGetSchemaUrlValidType()
    {
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturn('test');

        $this->schemaTypeMock->expects($this->any())
            ->method('getCode')->willReturn('test');

        $this->schemaTypeMock->expects($this->once())
            ->method('getSchemaUrlPath')
            ->willReturn('/test');

        $this->assertEquals('/test', $this->index->getSchemaUrl());
    }

    /**
     * Test that Swagger UI throws an exception if an invalid schema type is supplied.
     *
     * @covers \Magento\Swagger\Block\Index::getSchemaUrl()
     */
    public function testGetSchemaUrlInvalidType()
    {
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturn('invalid');

        $this->schemaTypeMock->expects($this->any())
            ->method('getCode')->willReturn('test');

        $this->expectException(\UnexpectedValueException::class);

        $this->index->getSchemaUrl();
    }
}
