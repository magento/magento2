<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Option;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Catalog\Model\Product\Option\Repository;
use Magento\Catalog\Model\Product\Option\SaveHandler;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Catalog\Model\Product\Option\SaveHandler.
 */
class SaveHandlerTest extends TestCase
{
    /**
     * @var SaveHandler|MockObject
     */
    protected $model;

    /**
     * @var Product|MockObject
     */
    protected $entity;

    /**
     * @var Option|MockObject
     */
    protected $optionMock;

    /**
     * @var Repository|MockObject
     */
    protected $optionRepository;

    /**
     * @var Relation|MockObject
     */
    private $relationMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->entity = $this->createMock(Product::class);
        $this->optionMock = $this->createMock(Option::class);
        $this->optionRepository = $this->createMock(Repository::class);
        $this->relationMock = $this->createMock(Relation::class);

        $this->model = new SaveHandler($this->optionRepository, $this->relationMock);
    }

    /**
     * Test for execute
     *
     * @return void
     */
    public function testExecute(): void
    {
        $this->optionMock->method('getOptionId')->willReturn(5);
        $this->entity->expects($this->once())->method('getOptions')->willReturn([$this->optionMock]);

        $secondOptionMock = $this->createMock(Option::class);
        $secondOptionMock->method('getOptionId')->willReturn(6);

        $this->optionRepository
            ->expects($this->once())
            ->method('getProductOptions')
            ->with($this->entity)
            ->willReturn([$this->optionMock, $secondOptionMock]);

        $this->optionRepository->expects($this->once())->method('delete');
        $this->optionRepository->expects($this->once())->method('save')->with($this->optionMock);

        $this->assertEquals($this->entity, $this->model->execute($this->entity));
    }

    /**
     * Ensure IDs are preserved when API option data does not contain them
     *
     * @return void
     */
    public function testExecuteResolvesMissingOptionAndValueIds(): void
    {
        $optionId = null;
        $valueId = null;
        $option = $this->createMock(Option::class);
        $option->method('getOptionId')->willReturnCallback(static function () use (&$optionId) {
            return $optionId;
        });
        $option->expects($this->once())->method('setOptionId')->with(5)
            ->willReturnCallback(function (int $id) use (&$optionId, $option) {
                $optionId = $id;
                return $option;
            });
        $option->method('getTitle')->willReturn('UOM');
        $option->method('getType')->willReturn('radio');
        $option->method('getIsRequire')->willReturn(false);

        $value = $this->createMock(Value::class);
        $value->method('getOptionTypeId')->willReturnCallback(static function () use (&$valueId) {
            return $valueId;
        });
        $value->expects($this->once())->method('setOptionTypeId')->with(10)
            ->willReturnCallback(function (int $id) use (&$valueId, $value) {
                $valueId = $id;
                return $value;
            });
        $value->method('getSku')->willReturn('PK');
        $option->method('getValues')->willReturn([$value]);

        $persistedOption = $this->createMock(Option::class);
        $persistedOption->method('getOptionId')->willReturn(5);
        $persistedOption->method('getTitle')->willReturn('UOM');
        $persistedOption->method('getType')->willReturn('radio');
        $persistedValue = $this->createMock(Value::class);
        $persistedValue->method('getOptionTypeId')->willReturn(10);
        $persistedValue->method('getSku')->willReturn('PK');
        $persistedOption->method('getValues')->willReturn([$persistedValue]);

        $this->entity->method('getOptions')->willReturn([$option]);
        $this->optionRepository->method('getProductOptions')->with($this->entity)
            ->willReturn([$persistedOption]);
        $this->optionRepository->expects($this->never())->method('delete');
        $this->optionRepository->expects($this->once())->method('save')->with($option)
            ->willReturnCallback(function () use (&$optionId, &$valueId): void {
                $this->assertSame(5, $optionId);
                $this->assertSame(10, $valueId);
            });

        $this->assertSame($this->entity, $this->model->execute($this->entity));
    }
}
