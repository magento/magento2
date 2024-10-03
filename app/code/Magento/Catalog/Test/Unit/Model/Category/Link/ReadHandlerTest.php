<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReadHandlerTest extends TestCase
{
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
        $this->categoryLinkFactory = $this->getMockBuilder(CategoryLinkInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->productCategoryLink = $this->getMockBuilder(CategoryLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

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
            $dtoCategoryLinks[$key] = $this->getMockBuilder(CategoryLinkInterface::class)->getMockForAbstractClass();
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

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getExtensionAttributes', 'setExtensionAttributes'])
            ->getMock();

        $extensionAttributes = $this->getMockBuilder(ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setCategoryLinks'])
            ->getMockForAbstractClass();
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
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getExtensionAttributes', 'setExtensionAttributes'])
            ->getMock();

        $extensionAttributes = $this->getMockBuilder(ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setCategoryLinks'])
            ->getMockForAbstractClass();
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
