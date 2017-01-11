<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Serialize\Serializer\Json;

class UrlRewriteHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlRewriteHandler
     */
    protected $urlRewriteHandler;

    /**
     * @var ChildrenCategoriesProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $childrenCategoriesProviderMock;

    /**
     * @var CategoryUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryUrlRewriteGeneratorMock;

    /**
     * @var ProductUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productUrlRewriteGeneratorMock;

    /**
     * @var UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlPersistMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->childrenCategoriesProviderMock = $this->getMockBuilder(ChildrenCategoriesProvider::class)
            ->getMock();
        $this->categoryUrlRewriteGeneratorMock = $this->getMockBuilder(CategoryUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productUrlRewriteGeneratorMock = $this->getMockBuilder(ProductUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlPersistMock = $this->getMockBuilder(UrlPersistInterface::class)
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlRewriteHandler = new UrlRewriteHandler(
            $this->childrenCategoriesProviderMock,
            $this->categoryUrlRewriteGeneratorMock,
            $this->productUrlRewriteGeneratorMock,
            $this->urlPersistMock,
            $this->collectionFactoryMock,
            $this->serializerMock
        );
    }

    public function testDeleteCategoryRewritesForChildren()
    {
        $category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $category->expects($this->once())
            ->method('getId')
            ->willReturn(2);

        $this->childrenCategoriesProviderMock->expects($this->once())
            ->method('getChildrenIds')
            ->with($category, true)
            ->willReturn([3, 4]);

        $this->serializerMock->expects($this->exactly(3))
            ->method('serialize');

        $this->urlRewriteHandler->deleteCategoryRewritesForChildren($category);
    }
}
