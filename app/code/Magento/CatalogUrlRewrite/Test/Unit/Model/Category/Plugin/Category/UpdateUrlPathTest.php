<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category\Plugin\Category;

/**
 * Unit test for Magento\CatalogUrlRewrite\Model\Category\Plugin\Category\UpdateUrlPath class
=======
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category\Plugin\Category;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;

/**
 * Unit test for Magento\CatalogUrlRewrite\Model\Category\Plugin\Category\UpdateUrlPath class.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
class UpdateUrlPathTest extends \PHPUnit\Framework\TestCase
{
    /**
<<<<<<< HEAD
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
=======
     * @var ObjectManager
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $objectManager;

    /**
<<<<<<< HEAD
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var CategoryUrlPathGenerator|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $categoryUrlPathGenerator;

    /**
<<<<<<< HEAD
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var CategoryUrlRewriteGenerator|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $categoryUrlRewriteGenerator;

    /**
<<<<<<< HEAD
     * @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var StoreViewService|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $storeViewService;

    /**
<<<<<<< HEAD
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var UrlPersistInterface|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $urlPersist;

    /**
<<<<<<< HEAD
     * @var \Magento\Catalog\Model\ResourceModel\Category|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var CategoryResource|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $categoryResource;

    /**
<<<<<<< HEAD
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var Category|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
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
=======
        $this->objectManager = new ObjectManager($this);
        $this->categoryUrlPathGenerator = $this->getMockBuilder(CategoryUrlPathGenerator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrlPath'])
            ->getMock();
        $this->categoryUrlRewriteGenerator = $this->getMockBuilder(CategoryUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();
        $this->categoryResource = $this->getMockBuilder(CategoryResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['saveAttribute'])
            ->getMock();
        $this->category = $this->getMockBuilder(Category::class)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
                    'setUrlPath'
                ]
            )
            ->getMock();
        $this->storeViewService = $this->getMockBuilder(\Magento\CatalogUrlRewrite\Service\V1\StoreViewService::class)
            ->disableOriginalConstructor()
            ->setMethods(['doesEntityHaveOverriddenUrlPathForStore'])
            ->getMock();
        $this->urlPersist = $this->getMockBuilder(\Magento\UrlRewrite\Model\UrlPersistInterface::class)
=======
                    'setUrlPath',
                ]
            )
            ->getMock();
        $this->storeViewService = $this->getMockBuilder(StoreViewService::class)
            ->disableOriginalConstructor()
            ->setMethods(['doesEntityHaveOverriddenUrlPathForStore'])
            ->getMock();
        $this->urlPersist = $this->getMockBuilder(UrlPersistInterface::class)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->disableOriginalConstructor()
            ->setMethods(['replace'])
            ->getMockForAbstractClass();

        $this->updateUrlPathPlugin = $this->objectManager->getObject(
            \Magento\CatalogUrlRewrite\Model\Category\Plugin\Category\UpdateUrlPath::class,
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'categoryUrlRewriteGenerator' => $this->categoryUrlRewriteGenerator,
                'urlPersist' => $this->urlPersist,
<<<<<<< HEAD
                'storeViewService' => $this->storeViewService
=======
                'storeViewService' => $this->storeViewService,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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

<<<<<<< HEAD
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPath')->with($this->category)
=======
        $this->categoryUrlPathGenerator
            ->expects($this->once())
            ->method('getUrlPath')
            ->with($this->category)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
                    [
                        $categoryStoreIds[1], $parentId, 'catalog_category', false
                    ],
                    [
                        $categoryStoreIds[2], $parentId, 'catalog_category', true
                    ]
                ]
            );
        $this->categoryResource->expects($this->once())->method('saveAttribute')->with($this->category, 'url_path')
=======
                    [$categoryStoreIds[1], $parentId, 'catalog_category', false],
                    [$categoryStoreIds[2], $parentId, 'catalog_category', true],
                ]
            );
        $this->categoryResource
            ->expects($this->once())
            ->method('saveAttribute')
            ->with($this->category, 'url_path')
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
