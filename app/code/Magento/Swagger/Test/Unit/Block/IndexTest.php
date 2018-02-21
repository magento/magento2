<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swagger\Test\Unit\Block;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Swagger\Api\SchemaTypeInterface;
use Magento\Swagger\Block\Index;
use Magento\Swagger\Block\SchemaTypes;

class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SchemaTypeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $schemaTypeMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Index
     */
    private $index;

    /**
     * @inheritdoc
     */
    protected function setUp()
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
                    'schema_types' => (new ObjectManager($this))->getObject(
                        SchemaTypes::class,
                        [
                            'types' => [$this->schemaTypeMock]
                        ]
                    )
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
     * Test that the passed URL parameter is not used when it is not a valid schema type.
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

        $this->schemaTypeMock->expects($this->once())
            ->method('getSchemaUrlPath')
            ->willReturn('/test');

        $this->assertEquals('/test', $this->index->getSchemaUrl());
    }
}
