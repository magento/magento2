<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Option;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
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
        $this->entity = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionRepository = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->relationMock = $this->getMockBuilder(Relation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new SaveHandler($this->optionRepository, $this->relationMock);
    }

    /**
     * Test for execute
     *
     * @return void
     */
    public function testExecute(): void
    {
        $this->optionMock->expects($this->any())->method('getOptionId')->willReturn(5);
        $this->entity->expects($this->once())->method('getOptions')->willReturn([$this->optionMock]);

        $secondOptionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $secondOptionMock->expects($this->once())->method('getOptionId')->willReturn(6);

        $this->optionRepository
            ->expects($this->once())
            ->method('getProductOptions')
            ->with($this->entity)
            ->willReturn([$this->optionMock, $secondOptionMock]);

        $this->optionRepository->expects($this->once())->method('delete');
        $this->optionRepository->expects($this->once())->method('save')->with($this->optionMock);

        $this->assertEquals($this->entity, $this->model->execute($this->entity));
    }
}
