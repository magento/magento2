<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\UiComponent\DataProvider;

use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    /**
     * @var AttributeValueFactory|MockObject
     */
    private $attributeValueFactory;

    /**
     * @var Document
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attributeValueFactory = $this->createMock(AttributeValueFactory::class);
        $this->model = new Document($this->attributeValueFactory);
    }

    public function testGetCustomAttribute(): void
    {
        $this->attributeValueFactory->expects($this->once())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new AttributeValue();
                }
            );
        $this->model->setData('attr1', 'val1');
        $this->model->setData('attr2', 'val2');
        $attr1 = $this->model->getCustomAttribute('attr1');
        $attr2 = $this->model->getCustomAttribute('attr2');
        $this->assertNotSame($attr1, $attr2);
        $this->assertEquals('val1', $attr1->getValue());
        $this->assertEquals('val2', $attr2->getValue());
    }

    public function testGetCustomAttributes(): void
    {
        $this->attributeValueFactory->expects($this->once())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new AttributeValue();
                }
            );
        $this->model->setData('attr1', 'val1');
        $this->model->setData('attr2', 'val2');
        $attributes = $this->model->getCustomAttributes();
        $this->assertCount(2, $attributes);
        $this->assertNotSame($attributes[0], $attributes[1]);
        $this->assertEquals('val1', $attributes[0]->getValue());
        $this->assertEquals('val2', $attributes[1]->getValue());
    }
}
