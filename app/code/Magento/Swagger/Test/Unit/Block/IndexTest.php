<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Swagger\Test\Unit\Block;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Swagger\Api\Data\SchemaTypeInterface;
use Magento\Swagger\Block\Index;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var SchemaTypeInterface|MockObject
     */
    private $schemaTypeMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Index
     */
    private $index;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $this->schemaTypeMock = $this->getMockBuilder(SchemaTypeInterface::class)
            ->getMock();
        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMock();

        $this->index = (new ObjectManager($this))->getObject(
            Index::class,
            [
                'context' => (new ObjectManager($this))->getObject(
                    Context::class,
                    [
                        'request' => $this->requestMock,
                        'urlBuilder' => $this->urlBuilder
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

        $this->urlBuilder->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn('');

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
