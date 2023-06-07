<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Option;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option\Converter;
use Magento\Catalog\Model\Product\Option\Repository;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Option;
use Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends TestCase
{
    /**
     * @var Repository
     */
    protected $optionRepository;

    /**
     * @var MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var MockObject
     */
    protected $optionResourceMock;

    /**
     * @var ProductCustomOptionInterface|MockObject
     */
    protected $optionMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $optionCollectionFactory;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->createMock(ProductRepository::class);
        $this->optionResourceMock = $this->createMock(Option::class);
        $converterMock = $this->createMock(Converter::class);
        $this->optionMock = $this->createMock(\Magento\Catalog\Model\Product\Option::class);
        $this->productMock = $this->createMock(Product::class);
        $optionFactory = $this->createPartialMock(OptionFactory::class, ['create']);
        $this->optionCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataPool->expects($this->any())->method('getMetadata')->willReturn($metadata);

        $this->optionRepository = new Repository(
            $this->productRepositoryMock,
            $this->optionResourceMock,
            $converterMock,
            $this->optionCollectionFactory,
            $optionFactory,
            $metadataPool
        );
    }

    /**
     * @return void
     */
    public function testGetList(): void
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

    /**
     * @return void
     */
    public function testDelete(): void
    {
        $this->optionResourceMock->expects($this->once())->method('delete')->with($this->optionMock);
        $this->assertTrue($this->optionRepository->delete($this->optionMock));
    }

    /**
     * @return void
     */
    public function testGetNonExistingOption(): void
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
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

    /**
     * @return void
     */
    public function testGet(): void
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
     * @return void
     */
    public function testDeleteByIdentifierNonExistingOption(): void
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
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

    /**
     * @return void
     */
    public function testDeleteByIdentifier(): void
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
     * @return void
     */
    public function testDeleteByIdentifierWhenCannotRemoveOption(): void
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
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
     * @return void
     */
    public function testSaveCouldNotSaveException(): void
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('The ProductSku is empty. Set the ProductSku and try again.');
        $this->optionMock->expects($this->once())->method('getProductSku')->willReturn(null);
        $this->optionRepository->save($this->optionMock);
    }

    /**
     * @return void
     */
    public function testSaveNoSuchEntityException(): void
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
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

    /**
     * @return void
     */
    public function testSave(): void
    {
        $productSku = 'simple_product';
        $optionId = 1;
        $originalValue1 = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $originalValue2 = clone $originalValue1;
        $originalValue3 = clone $originalValue1;

        $originalValue1
            ->method('getData')
            ->withConsecutive(['option_type_id'])
            ->willReturnOnConsecutiveCalls(10);
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
        $optionCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionCollection->expects($this->once())->method('getProductOptions')->willReturn([$this->optionMock]);
        $this->optionCollectionFactory->expects($this->once())->method('create')->willReturn($optionCollection);
        $this->optionMock->expects($this->exactly(2))->method('getValues')->willReturn([
            $originalValue1,
            $originalValue2,
            $originalValue3
        ]);
        $this->assertEquals($this->optionMock, $this->optionRepository->save($this->optionMock));
    }

    /**
     * @return void
     */
    public function testSaveWhenOptionTypeWasChanged(): void
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
        $optionCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionCollection->expects($this->once())->method('getProductOptions')->willReturn([$this->optionMock]);
        $this->optionCollectionFactory->expects($this->once())->method('create')->willReturn($optionCollection);
        $this->optionMock->expects($this->exactly(2))->method('getValues')->willReturn(null);
        $this->assertEquals($this->optionMock, $this->optionRepository->save($this->optionMock));
    }
}
