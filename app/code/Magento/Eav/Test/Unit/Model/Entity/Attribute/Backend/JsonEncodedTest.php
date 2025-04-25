<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Backend\JsonEncoded;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JsonEncodedTest extends TestCase
{
    /**
     * @var JsonEncoded
     */
    private $model;

    /**
     * @var Attribute|MockObject
     */
    private $attributeMock;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * Set up before test
     */
    protected function setUp(): void
    {
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['serialize', 'unserialize'])
            ->getMock();

        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttributeCode'])
            ->getMock();

        $this->attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('json_encoded');

        $this->model = new JsonEncoded($this->serializerMock);
        $this->model->setAttribute($this->attributeMock);
    }

    /**
     * Test before save handler
     */
    public function testBeforeSave()
    {
        $product = new DataObject(
            [
                'json_encoded' => [1, 2, 3]
            ]
        );
        $this->model->beforeSave($product);
        $this->assertEquals(json_encode([1, 2, 3]), $product->getData('json_encoded'));
    }

    /**
     * Test before save handler with already encoded attribute value
     */
    public function testBeforeSaveWithAlreadyEncodedValue()
    {
        $product = new DataObject(
            [
                'json_encoded' => [1, 2, 3]
            ]
        );

        // save twice
        $this->model->beforeSave($product);
        $this->model->beforeSave($product);

        // check it is encoded only once
        $this->assertEquals(json_encode([1, 2, 3]), $product->getData('json_encoded'));
    }

    /**
     * Test after load handler
     */
    public function testAfterLoad()
    {
        $product = new DataObject(
            [
                'json_encoded' => json_encode([1, 2, 3])
            ]
        );
        $this->model->afterLoad($product);
        $this->assertEquals([1, 2, 3], $product->getData('json_encoded'));
    }

    /**
     * Test after load handler with null attribute value
     */
    public function testAfterLoadWithNullAttributeValue()
    {
        $product = new DataObject(
            [
                'json_encoded' => null
            ]
        );
        $this->model->afterLoad($product);
        $this->assertEquals([], $product->getData('json_encoded'));
    }
}
