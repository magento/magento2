<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Bundle\Api\ProductOptionRepositoryInterface;
use Magento\Bundle\Model\Product\SaveHandler;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Unit tests for \Magento\Bundle\Model\Product\SaveHandler class.
 */
class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductInterface|MockObject
     */
    private $productMock;

    /**
     * @var ProductExtensionInterface|MockObject
     */
    private $productExtensionMock;

    /**
     * @var OptionInterface|MockObject
     */
    private $optionMock;

    /**
     * @var ProductOptionRepositoryInterface|MockObject
     */
    private $optionRepositoryMock;

    /**
     * @var ProductLinkManagementInterface|MockObject
     */
    private $productLinkManagementMock;

    /**
     * @var LinkInterface|MockObject
     */
    private $linkMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var EntityMetadataInterface|MockObject
     */
    private $metadataMock;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(
                [
                    'getExtensionAttributes',
                    'getCopyFromView',
                    'getData',
                    'getTypeId',
                    'getSku',
                ]
            )
            ->getMockForAbstractClass();
        $this->productExtensionMock = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['getBundleProductOptions'])
            ->getMockForAbstractClass();
        $this->optionMock = $this->getMockBuilder(OptionInterface::class)
            ->setMethods(
                [
                    'setParentId',
                    'getId',
                    'getOptionId',
                ]
            )
            ->getMockForAbstractClass();
        $this->optionRepositoryMock = $this->createMock(ProductOptionRepositoryInterface::class);
        $this->productLinkManagementMock = $this->createMock(ProductLinkManagementInterface::class);
        $this->linkMock = $this->createMock(LinkInterface::class);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->metadataMock = $this->createMock(EntityMetadataInterface::class);
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->willReturn($this->metadataMock);

        $this->saveHandler = $this->objectManager->getObject(
            SaveHandler::class,
            [
                'optionRepository' => $this->optionRepositoryMock,
                'productLinkManagement' => $this->productLinkManagementMock,
                'metadataPool' => $this->metadataPoolMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteWithInvalidProductType()
    {
        $productType = 'simple';

        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getBundleProductOptions')
            ->willReturn([]);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);

        $entity = $this->saveHandler->execute($this->productMock);
        $this->assertSame($this->productMock, $entity);
    }

    /**
     * @return void
     */
    public function testExecuteWithoutExistingOption()
    {
        $productType = 'bundle';
        $productSku = 'product-sku';
        $optionId = null;

        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getBundleProductOptions')
            ->willReturn([$this->optionMock]);

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);

        $this->productMock->expects($this->once())
            ->method('getSku')
            ->willReturn($productSku);
        $this->optionRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($productSku)
            ->willReturn([]);

        $this->optionMock->expects($this->any())
            ->method('getOptionId')
            ->willReturn($optionId);

        $this->productMock->expects($this->once())
            ->method('getCopyFromView')
            ->willReturn(false);

        $this->optionMock->expects($this->never())->method('setOptionId');
        $this->optionRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->productMock, $this->optionMock)
            ->willReturn($optionId);

        $this->saveHandler->execute($this->productMock);
    }

    /**
     * @return void
     */
    public function testExecuteWithExistingOption()
    {
        $productType = 'bundle';
        $productSku = 'product-sku';
        $productLinkSku = 'product-link-sku';
        $linkField = 'entity_id';
        $parentId = 1;
        $existingOptionId = 1;
        $optionId = 2;

        /** @var OptionInterface|MockObject $existingOptionMock */
        $existingOptionMock = $this->getMockBuilder(OptionInterface::class)
            ->setMethods(['getOptionId'])
            ->getMockForAbstractClass();

        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getBundleProductOptions')
            ->willReturn([$this->optionMock]);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);

        $this->productMock->expects($this->exactly(3))
            ->method('getSku')
            ->willReturn($productSku);
        $this->optionRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($productSku)
            ->willReturn([$existingOptionMock]);

        $existingOptionMock->expects($this->any())
            ->method('getOptionId')
            ->willReturn($existingOptionId);
        $this->optionMock->expects($this->any())
            ->method('getOptionId')
            ->willReturn($optionId);

        $this->productMock->expects($this->once())
            ->method('getCopyFromView')
            ->willReturn(false);
        $this->metadataMock->expects($this->once())
            ->method('getLinkField')
            ->willReturn($linkField);
        $this->productMock->expects($this->once())
            ->method('getData')
            ->with($linkField)
            ->willReturn($parentId);

        $this->optionRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku, $existingOptionId)
            ->willReturn($this->optionMock);
        $this->optionMock->expects($this->once())
            ->method('setParentId')
            ->with($parentId)
            ->willReturnSelf();
        $this->optionMock->expects($this->once())
            ->method('getProductLinks')
            ->willReturn([$this->linkMock]);
        $this->linkMock->expects($this->once())
            ->method('getSku')
            ->willReturn($productLinkSku);

        $this->optionMock->expects($this->any())
            ->method('getId')
            ->willReturn($existingOptionId);
        $this->productLinkManagementMock->expects($this->once())
            ->method('removeChild')
            ->with($productSku, $existingOptionId, $productLinkSku)
            ->willReturn(true);
        $this->optionRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($this->optionMock)
            ->willReturn(true);

        $this->optionRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->productMock, $this->optionMock)
            ->willReturn($optionId);

        $this->saveHandler->execute($this->productMock);
    }
}
