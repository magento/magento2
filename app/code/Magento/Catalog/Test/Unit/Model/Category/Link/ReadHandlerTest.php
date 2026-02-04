<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category\Link;

use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Catalog\Api\Data\CategoryLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Category\Link\ReadHandler;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CategoryLink;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReadHandlerTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var CategoryLinkInterfaceFactory|MockObject
     */
    private $categoryLinkFactory;

    /**
     * @var CategoryLink|MockObject
     */
    private $productCategoryLink;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->categoryLinkFactory = $this->createPartialMock(
            CategoryLinkInterfaceFactory::class,
            ['create']
        );
        $this->productCategoryLink = $this->createMock(CategoryLink::class);
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);

        $this->readHandler = new ReadHandler(
            $this->categoryLinkFactory,
            $this->dataObjectHelper,
            $this->productCategoryLink
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $categoryLinks = [
            ['category_id' => 3, 'position' => 10],
            ['category_id' => 4, 'position' => 20]
        ];

        $dtoCategoryLinks = [];
        $dataObjHelperWithArgs = $categoryLinkFactoryWillReturnArgs = [];

        foreach ($categoryLinks as $key => $categoryLink) {
            $dtoCategoryLinks[$key] = $this->createMock(CategoryLinkInterface::class);
            $dataObjHelperWithArgs[] = [$dtoCategoryLinks[$key], $categoryLink, CategoryLinkInterface::class];
            $categoryLinkFactoryWillReturnArgs[] = $dtoCategoryLinks[$key];
        }
        $this->dataObjectHelper
            ->method('populateWithArray')
            ->willReturnCallback(function (...$dataObjHelperWithArgs) {
                return null;
            });
        
        $this->categoryLinkFactory
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$categoryLinkFactoryWillReturnArgs);

        $product = $this->createPartialMock(Product::class, ['getExtensionAttributes', 'setExtensionAttributes']);

        /** @var ProductExtensionInterface $extensionAttributes */
        $extensionAttributes = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getCategoryLinks', 'setCategoryLinks']
        );
        $extensionAttributes->method('getCategoryLinks')->willReturn($dtoCategoryLinks);
        $extensionAttributes->expects(static::once())->method('setCategoryLinks')->with($dtoCategoryLinks);

        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $product->expects(static::once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributes);

        $this->productCategoryLink->expects(static::any())
            ->method('getCategoryLinks')
            ->with($product)
            ->willReturn($categoryLinks);

        $entity = $this->readHandler->execute($product);
        static::assertSame($product, $entity);
    }

    /**
     * @return void
     */
    public function testExecuteNullExtensionAttributes(): void
    {
        $product = $this->createPartialMock(Product::class, ['getExtensionAttributes', 'setExtensionAttributes']);

        /** @var ProductExtensionInterface $extensionAttributes */
        $extensionAttributes = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getCategoryLinks', 'setCategoryLinks']
        );
        $extensionAttributes->method('getCategoryLinks')->willReturn(null);
        $extensionAttributes->expects(static::once())->method('setCategoryLinks')->with(null);

        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $product->expects(static::once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributes);

        $this->productCategoryLink->expects(static::any())
            ->method('getCategoryLinks')
            ->with($product)
            ->willReturn([]);

        $entity = $this->readHandler->execute($product);
        static::assertSame($product, $entity);
    }
}
