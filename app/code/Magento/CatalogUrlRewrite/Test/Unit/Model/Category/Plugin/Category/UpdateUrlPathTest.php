<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category\Plugin\Category;

/**
 * Unit test for Magento\CatalogUrlRewrite\Model\Category\Plugin\Category\UpdateUrlPath class
 */
class UpdateUrlPathTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryUrlPathGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryUrlRewriteGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeViewService;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlPersist;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryResource;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $category;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Category\Plugin\Category\UpdateUrlPath
     */
    private $updateUrlPathPlugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->categoryUrlPathGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getUrlPath'])
            ->getMock();
        $this->categoryUrlRewriteGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();
        $this->categoryResource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category::class)
            ->disableOriginalConstructor()
            ->setMethods(['saveAttribute'])
            ->getMock();
        $this->category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getStoreId',
                    'getParentId',
                    'isObjectNew',
                    'isInRootCategoryList',
                    'getStoreIds',
                    'setStoreId',
                    'unsUrlPath',
                    'setUrlPath'
                ]
            )
            ->getMock();
        $this->storeViewService = $this->getMockBuilder(\Magento\CatalogUrlRewrite\Service\V1\StoreViewService::class)
            ->disableOriginalConstructor()
            ->setMethods(['doesEntityHaveOverriddenUrlPathForStore'])
            ->getMock();
        $this->urlPersist = $this->getMockBuilder(\Magento\UrlRewrite\Model\UrlPersistInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['replace'])
            ->getMockForAbstractClass();

        $this->updateUrlPathPlugin = $this->objectManager->getObject(
            \Magento\CatalogUrlRewrite\Model\Category\Plugin\Category\UpdateUrlPath::class,
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'categoryUrlRewriteGenerator' => $this->categoryUrlRewriteGenerator,
                'urlPersist' => $this->urlPersist,
                'storeViewService' => $this->storeViewService
            ]
        );
    }

    public function testAroundSaveWithoutRootCategory()
    {
        $this->category->expects($this->atLeastOnce())->method('getParentId')->willReturn(0);
        $this->category->expects($this->atLeastOnce())->method('isObjectNew')->willReturn(true);
        $this->category->expects($this->atLeastOnce())->method('isInRootCategoryList')->willReturn(false);
        $this->category->expects($this->never())->method('getStoreIds');

        $this->assertEquals(
            $this->categoryResource,
            $this->updateUrlPathPlugin->afterSave($this->categoryResource, $this->categoryResource, $this->category)
        );
    }

    public function testAroundSaveWithRootCategory()
    {
        $parentId = 1;
        $categoryStoreIds = [0,1,2];
        $generatedUrlPath = 'parent_category/child_category';

        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPath')->with($this->category)
            ->willReturn($generatedUrlPath);
        $this->category->expects($this->atLeastOnce())->method('getParentId')->willReturn($parentId);
        $this->category->expects($this->atLeastOnce())->method('isObjectNew')->willReturn(true);
        $this->category->expects($this->atLeastOnce())->method('isInRootCategoryList')->willReturn(false);
        $this->category->expects($this->atLeastOnce())->method('getStoreIds')->willReturn($categoryStoreIds);
        $this->category->expects($this->once())->method('setStoreId')->with($categoryStoreIds[2])->willReturnSelf();
        $this->category->expects($this->once())->method('unsUrlPath')->willReturnSelf();
        $this->category->expects($this->once())->method('setUrlPath')->with($generatedUrlPath)->willReturnSelf();
        $this->storeViewService->expects($this->exactly(2))->method('doesEntityHaveOverriddenUrlPathForStore')
            ->willReturnMap(
                [
                    [
                        $categoryStoreIds[1], $parentId, 'catalog_category', false
                    ],
                    [
                        $categoryStoreIds[2], $parentId, 'catalog_category', true
                    ]
                ]
            );
        $this->categoryResource->expects($this->once())->method('saveAttribute')->with($this->category, 'url_path')
            ->willReturnSelf();
        $generatedUrlRewrite = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryUrlRewriteGenerator->expects($this->once())->method('generate')->with($this->category)
            ->willReturn([$generatedUrlRewrite]);
        $this->urlPersist->expects($this->once())->method('replace')->with([$generatedUrlRewrite])->willReturnSelf();

        $this->assertEquals(
            $this->categoryResource,
            $this->updateUrlPathPlugin->afterSave($this->categoryResource, $this->categoryResource, $this->category)
        );
    }
}
