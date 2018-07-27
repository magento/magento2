<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer;
use Magento\CatalogUrlRewrite\Observer\CategoryProcessUrlRewriteSavingObserver;
use Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Tests CategoryProcessUrlRewriteSavingObserver class.
 */
class CategoryProcessUrlRewriteSavingObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CategoryProcessUrlRewriteSavingObserver
     */
    private $observerModel;

    /**
     * @var CategoryUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryUrlRewriteGeneratorMock;

    /**
     * @var UrlRewriteHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlRewriteHandlerMock;

    /**
     * @var UrlRewriteBunchReplacer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlRewriteBunchReplacerMock;

    /**
     * @var DatabaseMapPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $databaseMapPoolMock;

    private $dataUrlRewriteClassNames = [
        DataCategoryUrlRewriteDatabaseMap::class,
        DataProductUrlRewriteDatabaseMap::class
    ];

    protected function setUp()
    {
        $this->categoryUrlRewriteGeneratorMock = $this->getMock(
            CategoryUrlRewriteGenerator::class,
            ['generate'],
            [],
            '',
            false
        );
        $this->urlRewriteHandlerMock = $this->getMock(
            UrlRewriteHandler::class,
            ['generateProductUrlRewrites'],
            [],
            '',
            false
        );
        $this->urlRewriteBunchReplacerMock = $this->getMock(
            UrlRewriteBunchReplacer::class,
            ['doBunchReplace'],
            [],
            '',
            false
        );
        $this->databaseMapPoolMock = $this->getMock(DatabaseMapPool::class, ['resetMap'], [], '', false);

        $this->objectManager = new ObjectManager($this);
        $this->observerModel = $this->objectManager->getObject(
            CategoryProcessUrlRewriteSavingObserver::class,
            [
                'categoryUrlRewriteGenerator' => $this->categoryUrlRewriteGeneratorMock,
                'urlRewriteHandler' => $this->urlRewriteHandlerMock,
                'urlRewriteBunchReplacer' => $this->urlRewriteBunchReplacerMock,
                'databaseMapPool' => $this->databaseMapPoolMock,
                'dataUrlRewriteClassNames' => $this->dataUrlRewriteClassNames
            ]
        );
    }

    /**
     * Covers execite() method.
     *
     * @dataProvider executeDataProvider
     * @return void
     */
    public function testExecute(
        $parentId,
        $isChangedUrlKey,
        $isChangedIsAnchor,
        $isChangedProductList
    ) {
        $categoryId = 6;
        /** @var \PHPUnit_Framework_MockObject_MockObject $category */
        $category = $this->getMock(
            \Magento\Catalog\Model\Category::class,
            [
                'getEntityId',
                'getParentId',
                'dataHasChangedFor',
                'getIsChangedProductList'
            ],
            [],
            '',
            false
        );
        $category->expects($this->any())->method('getEntityId')->willReturn($categoryId);
        $category->expects($this->once())->method('getParentId')->willReturn($parentId);
        $category->expects($this->any())->method('dataHasChangedFor')
             ->willReturnMap(
                 [
                     ['url_key', $isChangedUrlKey],
                     ['is_anchor', $isChangedIsAnchor]
                 ]
             );
        $category->expects($this->any())->method('getIsChangedProductList')->willReturn($isChangedProductList);

        $categoryUrlRewriteResult = $this->getCategoryUrlRewriteResult();
        $this->categoryUrlRewriteGeneratorMock
            ->expects($this->any())
            ->method('generate')
            ->with($category)
            ->willReturn($categoryUrlRewriteResult);
        $productUrlRewriteResult = $this->getProductUrlRewriteResult();
        $this->urlRewriteHandlerMock
            ->expects($this->any())
            ->method('generateProductUrlRewrites')
            ->with($category)
            ->willReturn($productUrlRewriteResult);
        $this->urlRewriteBunchReplacerMock
            ->expects($this->any())
            ->method('doBunchReplace');
        $this->databaseMapPoolMock
            ->expects($this->any())
            ->method('resetMap');

        $event = $this->getMock(\Magento\Framework\Event::class, ['getData'], [], '', false);
        $event->expects($this->any())->method('getData')->with('category')->willReturn($category);
        $observer = $this->getMock(
            \Magento\Framework\Event\Observer::class,
            ['getEvent'],
            [],
            '',
            false
        );
        $observer->expects($this->any())->method('getEvent')->willReturn($event);

        $this->observerModel->execute($observer);
    }

    /**
     * Data provider for testExecute().
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'tree_root_category_parent' => [
                'parentId' => \Magento\Catalog\Model\Category::TREE_ROOT_ID,
                'isChangedUrlKey' => true,
                'isChangedIsAnchor' => true,
                'isChangedProductList' => true,
            ],
            'true_category_data' => [
                'parentId' => 3,
                'isChangedUrlKey' => true,
                'isChangedIsAnchor' => true,
                'isChangedProductList' => true,
            ],
            'false_category_data' => [
                'parentId' => 3,
                'isChangedUrlKey' => true,
                'isChangedIsAnchor' => true,
                'isChangedProductList' => true,
            ],
            'true_isChangedUrlKey' => [
                'parentId' => 3,
                'isChangedUrlKey' => true,
                'isChangedIsAnchor' => false,
                'isChangedProductList' => false,
            ],
            'true_isChangedIsAnchor' => [
                'parentId' => 3,
                'isChangedUrlKey' => false,
                'isChangedIsAnchor' => true,
                'isChangedProductList' => false,
            ],
            'true_isChangedProductList' => [
                'parentId' => 3,
                'isChangedUrlKey' => false,
                'isChangedIsAnchor' => false,
                'isChangedProductList' => true,
            ],
        ];
    }

    /**
     * Returns category urlRewrite result.
     *
     * @return array
     */
    private function getCategoryUrlRewriteResult()
    {
        $categoryUrlRewriteResult1 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $categoryUrlRewriteResult1->setRequestPath('category2.html')
            ->setStoreId(1)
            ->setEntityType('category')
            ->setEntityId(6)
            ->setTargetPath('catalog/category/view/id/6');
        $categoryUrlRewriteResult = [
            'category2.html_1' => $categoryUrlRewriteResult1,
        ];

        return $categoryUrlRewriteResult;
    }

    /**
     * Returns products urlRewrite result.
     *
     * @return array
     */
    private function getProductUrlRewriteResult()
    {
        $productUrlRewriteResult1 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $productUrlRewriteResult1->setEntityType('product')
            ->setEntityId(1)
            ->setRequestPath('simple1.html')
            ->setTargetPath('catalog/product/view/id/1')
            ->setStoreId(1);
        $productUrlRewriteResult2 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $productUrlRewriteResult2->setEntityType('product')
            ->setEntityId(1)
            ->setRequestPath('category2/simple1.html')
            ->setTargetPath('catalog/product/view/id/1/category/6')
            ->setStoreId(1)
            ->setMetadata('a:1:{s:11:"category_id";s:1:"6";}');

        $productUrlRewriteResult = [
            'simple1.html_1' => $productUrlRewriteResult1,
            'category2/simple1.html_1' => $productUrlRewriteResult2,
        ];

        return $productUrlRewriteResult;
    }
}
