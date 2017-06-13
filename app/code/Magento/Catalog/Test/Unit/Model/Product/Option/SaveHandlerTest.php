<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Option;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use \Magento\Catalog\Model\Product\Option\Repository;
use \Magento\Catalog\Model\Product\Option\SaveHandler;

class SaveHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SaveHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entity;

    /**
     * @var Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMock;

    /**
     * @var Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionRepository;

    public function setUp()
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

        $this->model = new SaveHandler($this->optionRepository);
    }

    public function testExecute()
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
