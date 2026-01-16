<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Bundle\Api\ProductOptionRepositoryInterface as OptionRepository;
use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Model\Option\SaveAction;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\Product\SaveHandler;
use Magento\Bundle\Model\Product\CheckOptionLinkIfExist;
use Magento\Bundle\Model\ProductRelationsProcessorComposite;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Bundle\Model\Product\SaveHandler
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ProductLinkManagementInterface|MockObject
     */
    private $productLinkManagement;

    /**
     * @var OptionRepository|MockObject
     */
    private $optionRepository;

    /**
     * @var SaveAction|MockObject
     */
    private $optionSave;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var CheckOptionLinkIfExist|MockObject
     */
    private $checkOptionLinkIfExist;

    /**
     * @var ProductRelationsProcessorComposite|MockObject
     */
    private $productRelationsProcessorComposite;

    /**
     * @var Product|MockObject
     */
    private $entity;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    protected function setUp(): void
    {
        $this->productLinkManagement = $this->createMock(ProductLinkManagementInterface::class);
        $this->optionRepository = $this->createMock(OptionRepository::class);
        $this->optionSave = $this->createMock(SaveAction::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->checkOptionLinkIfExist = $this->createMock(CheckOptionLinkIfExist::class);
        $this->productRelationsProcessorComposite = $this->createMock(
            ProductRelationsProcessorComposite::class
        );
        $this->entity = $this->createPartialMockWithReflection(
            Product::class,
            [
                'getTypeId', 'setTypeId', 'getExtensionAttributes', 'setExtensionAttributes',
                'getSku', 'getDropOptions', 'getCopyFromView', 'setCopyFromView'
            ]
        );
        $this->entity->method('getTypeId')->willReturn(Type::TYPE_CODE);
        $this->entity->method('getSku')->willReturn('test-sku');
        $this->entity->method('getDropOptions')->willReturn(false);
        $this->entity->method('getCopyFromView')->willReturn(false);

        $this->saveHandler = new SaveHandler(
            $this->optionRepository,
            $this->productLinkManagement,
            $this->optionSave,
            $this->metadataPool,
            $this->checkOptionLinkIfExist,
            $this->productRelationsProcessorComposite
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws Exception
     */
    public function testExecuteWithBulkOptionsProcessing(): void
    {
        $option = $this->createMock(OptionInterface::class);
        $option->method('getOptionId')->willReturn(1);
        $bundleOptions = [$option];

        $extensionAttributes = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getBundleProductOptions', 'setBundleProductOptions']
        );
        $extensionAttributes->method('getBundleProductOptions')->willReturn($bundleOptions);
        $this->entity->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $metadata = $this->createMock(EntityMetadataInterface::class);
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);
        $this->optionRepository->method('getList')->willReturn($bundleOptions);

        $this->optionSave->expects($this->once())
            ->method('saveBulk');
        $this->saveHandler->execute($this->entity);
    }
}
