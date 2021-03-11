<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Option;

use \Magento\Catalog\Model\Product\Option\Repository;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Repository
     */
    protected $optionRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $optionResourceMock;

    /**
     * @var ProductCustomOptionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $optionMock;

    /**
     * @var CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $optionCollectionFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->createMock(\Magento\Catalog\Model\ProductRepository::class);
        $this->optionResourceMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Option::class);
        $this->converterMock = $this->createMock(\Magento\Catalog\Model\Product\Option\Converter::class);
        $this->optionMock = $this->createMock(\Magento\Catalog\Model\Product\Option::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $optionFactory = $this->createPartialMock(\Magento\Catalog\Model\Product\OptionFactory::class, ['create']);
        $this->optionCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataPool->expects($this->any())->method('getMetadata')->willReturn($metadata);

        $this->optionRepository = new Repository(
            $this->productRepositoryMock,
            $this->optionResourceMock,
            $this->converterMock,
            $this->optionCollectionFactory,
            $optionFactory,
            $metadataPool
        );
    }

    public function testGetList()
    {
        $productSku = 'simple_product';
        $expectedResult = ['Expected_option'];
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->optionRepository->getList($productSku));
    }

    public function testDelete()
    {
        $this->optionResourceMock->expects($this->once())->method('delete')->with($this->optionMock);
        $this->assertTrue($this->optionRepository->delete($this->optionMock));
    }

    /**
     */
    public function testGetNonExistingOption()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, false)
            ->willReturn($this->productMock);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)
            ->willReturn(null);
        $this->optionRepository->get($productSku, $optionId);
    }

    public function testGet()
    {
        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, false)
            ->willReturn($this->productMock);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)->willReturn($this->optionMock);
        $this->assertEquals($this->optionMock, $this->optionRepository->get($productSku, $optionId));
    }

    /**
     */
    public function testDeleteByIdentifierNonExistingOption()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn([$this->optionMock]);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)
            ->willReturn(null);
        $this->optionRepository->deleteByIdentifier($productSku, $optionId);
    }

    public function testDeleteByIdentifier()
    {
        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn([$optionId => $this->optionMock]);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)
            ->willReturn($this->optionMock);
        $this->optionResourceMock->expects($this->once())->method('delete')->with($this->optionMock);
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->assertTrue($this->optionRepository->deleteByIdentifier($productSku, $optionId));
    }

    /**
     */
    public function testDeleteByIdentifierWhenCannotRemoveOption()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);

        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn([$optionId => $this->optionMock]);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)
            ->willReturn($this->optionMock);
        $this->optionResourceMock->expects($this->once())->method('delete')->with($this->optionMock);
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->productMock)
            ->willThrowException(new \Exception());
        $this->assertTrue($this->optionRepository->deleteByIdentifier($productSku, $optionId));
    }

    /**
     */
    public function testSaveCouldNotSaveException()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage('The ProductSku is empty. Set the ProductSku and try again.');

        $this->optionMock->expects($this->once())->method('getProductSku')->willReturn(null);
        $this->optionRepository->save($this->optionMock);
    }

    /**
     */
    public function testSaveNoSuchEntityException()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

        $productSku = 'simple_product';
        $optionId = 1;
        $productOptionId = 2;
        $this->optionMock->expects($this->once())->method('getProductSku')->willReturn($productSku);
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $productOption = clone $this->optionMock;
        $this->optionMock->expects($this->any())->method('getOptionId')->willReturn($optionId);
        $productOption->expects($this->any())->method('getOptionId')->willReturn($productOptionId);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn([$productOption]);
        $this->optionRepository->save($this->optionMock);
    }

    public function testSave()
    {
        $productSku = 'simple_product';
        $optionId = 1;
        $originalValue1 = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $originalValue2 = clone $originalValue1;
        $originalValue3 = clone $originalValue1;

        $originalValue1->expects($this->at(0))->method('getData')->with('option_type_id')->willReturn(10);
        $originalValue1->expects($this->once())->method('setData')->with('is_delete', 1);
        $originalValue2->expects($this->once())->method('getData')->with('option_type_id')->willReturn(4);
        $originalValue3->expects($this->once())->method('getData')->with('option_type_id')->willReturn(5);

        $this->optionMock->expects($this->once())->method('getProductSku')->willReturn($productSku);
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->optionMock->expects($this->any())->method('getOptionId')->willReturn($optionId);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn([]);
        $this->optionMock->expects($this->once())->method('getData')->with('values')->willReturn([
            ['option_type_id' => 4],
            ['option_type_id' => 5]
        ]);
        $optionCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Option\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionCollection->expects($this->once())->method('getProductOptions')->willReturn([$this->optionMock]);
        $this->optionCollectionFactory->expects($this->once())->method('create')->willReturn($optionCollection);
        $this->optionMock->expects($this->once())->method('getValues')->willReturn([
            $originalValue1,
            $originalValue2,
            $originalValue3
        ]);
        $this->assertEquals($this->optionMock, $this->optionRepository->save($this->optionMock));
    }

    public function testSaveWhenOptionTypeWasChanged()
    {
        $productSku = 'simple_product';
        $optionId = 1;
        $this->optionMock->expects($this->once())->method('getProductSku')->willReturn($productSku);
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->optionMock->expects($this->any())->method('getOptionId')->willReturn($optionId);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn([]);
        $this->optionMock->expects($this->once())->method('getData')->with('values')->willReturn([
            ['option_type_id' => 4],
            ['option_type_id' => 5]
        ]);
        $optionCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Option\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionCollection->expects($this->once())->method('getProductOptions')->willReturn([$this->optionMock]);
        $this->optionCollectionFactory->expects($this->once())->method('create')->willReturn($optionCollection);
        $this->optionMock->expects($this->once())->method('getValues')->willReturn(null);
        $this->assertEquals($this->optionMock, $this->optionRepository->save($this->optionMock));
    }
}
