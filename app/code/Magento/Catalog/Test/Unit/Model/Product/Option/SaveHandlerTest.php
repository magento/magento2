<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Option;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Repository;
use Magento\Catalog\Model\Product\Option\SaveHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveHandlerTest extends TestCase
{
    /**
     * @var SaveHandler
     */
    private $model;

    /**
     * @var Product|MockObject
     */
    private $entityMock;

    /**
     * @var Option|MockObject
     */
    private $optionMock;

    /**
     * @var Repository|MockObject
     */
    private $optionRepositoryMock;

    protected function setUp(): void
    {
        $this->entityMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptionsSaved', 'getCanSaveCustomOptions', 'getOptions', 'dataHasChangedFor', 'getSku'])
            ->getMock();
        $this->optionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionRepositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new SaveHandler($this->optionRepositoryMock);
    }

    public function testExecute(): void
    {
        $this->entityMock->expects($this->once())->method('getOptionsSaved')->willReturn(false);
        $this->entityMock->expects($this->once())->method('getCanSaveCustomOptions')->willReturn(true);
        $this->optionMock->expects($this->any())->method('getOptionId')->willReturn(5);
        $this->entityMock->expects($this->once())->method('getOptions')->willReturn([$this->optionMock]);

        $secondOptionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $secondOptionMock->expects($this->once())->method('getOptionId')->willReturn(6);

        $this->optionRepositoryMock
            ->expects($this->once())
            ->method('getProductOptions')
            ->with($this->entityMock)
            ->willReturn([$this->optionMock, $secondOptionMock]);

        $this->optionRepositoryMock->expects($this->once())->method('delete');
        $this->optionRepositoryMock->expects($this->once())->method('save')->with($this->optionMock);

        $this->assertEquals($this->entityMock, $this->model->execute($this->entityMock));
    }
}
