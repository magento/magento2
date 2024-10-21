<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
     * @var ProductInterface|MockObject
     */
    private $entity;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    protected function setUp(): void
    {
        $this->productLinkManagement = $this->getMockBuilder(ProductLinkManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionRepository = $this->getMockBuilder(OptionRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionSave = $this->getMockBuilder(SaveAction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkOptionLinkIfExist = $this->getMockBuilder(CheckOptionLinkIfExist::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRelationsProcessorComposite = $this->getMockBuilder(ProductRelationsProcessorComposite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entity = $this->getMockBuilder(ProductInterface::class)
            ->addMethods(['getCopyFromView', 'getData'])
            ->getMockForAbstractClass();
        $this->entity->expects($this->any())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_CODE);

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
     */
    public function testExecuteWithBulkOptionsProcessing(): void
    {
        $option = $this->getMockBuilder(OptionInterface::class)
            ->onlyMethods(['getOptionId'])
            ->getMockForAbstractClass();
        $option->expects($this->any())
            ->method('getOptionId')
            ->willReturn(1);
        $bundleOptions = [$option];

        $extensionAttributes = $this->getMockBuilder(ProductExtensionInterface::class)
            ->addMethods(['getBundleProductOptions'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->any())
            ->method('getBundleProductOptions')
            ->willReturn($bundleOptions);
        $this->entity->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $metadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);
        $this->optionRepository->expects($this->any())
            ->method('getList')
            ->willReturn($bundleOptions);

        $this->optionSave->expects($this->once())
            ->method('saveBulk');
        $this->saveHandler->execute($this->entity);
    }
}
