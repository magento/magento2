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
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\Catalog\Test\Unit\Helper\ProductExtensionTestHelper;

/**
 * Test class for \Magento\Bundle\Model\Product\SaveHandler
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends TestCase
{
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
     * @var ProductTestHelper
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
        $this->entity = new ProductTestHelper();
        $this->entity->setTypeId(Type::TYPE_CODE);

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

        $extensionAttributes = new ProductExtensionTestHelper();
        $extensionAttributes->setBundleProductOptions($bundleOptions);
        $this->entity->setExtensionAttributes($extensionAttributes);
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
