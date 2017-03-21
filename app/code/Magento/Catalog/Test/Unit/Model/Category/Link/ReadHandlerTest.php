<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Category\Link;

use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Catalog\Api\Data\CategoryLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Category\Link\ReadHandler;
use Magento\Catalog\Model\ResourceModel\Product\CategoryLink;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Catalog\Model\Product;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ReadHandlerTest
 */
class ReadHandlerTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
        $this->categoryLinkFactory = $this->getMockBuilder(CategoryLinkInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
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

    public function testExecute()
    {
        $categoryLinks = [
            ['category_id' => 3, 'position' => 10],
            ['category_id' => 4, 'position' => 20]
        ];

        $dtoCategoryLinks = [];
        foreach ($categoryLinks as $key => $categoryLink) {
            $dtoCategoryLinks[$key] = $this->getMockBuilder(CategoryLinkInterface::class)
                ->getMockForAbstractClass();
            $this->dataObjectHelper->expects(static::at($key))
                ->method('populateWithArray')
                ->with($dtoCategoryLinks[$key], $categoryLink, CategoryLinkInterface::class);
            $this->categoryLinkFactory->expects(static::at($key))
                ->method('create')
                ->willReturn($dtoCategoryLinks[$key]);
        }

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes', 'setExtensionAttributes'])
            ->getMock();

        $extensionAttributes = $this->getMockBuilder(ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCategoryLinks'])
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

    public function testExecuteNullExtensionAttributes()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes', 'setExtensionAttributes'])
            ->getMock();

        $extensionAttributes = $this->getMockBuilder(ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCategoryLinks'])
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
