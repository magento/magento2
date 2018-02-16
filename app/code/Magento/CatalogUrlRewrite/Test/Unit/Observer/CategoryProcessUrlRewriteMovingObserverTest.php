<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\CatalogUrlRewrite\Block\UrlKeyRenderer;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer;
use Magento\CatalogUrlRewrite\Observer\CategoryProcessUrlRewriteMovingObserver;
use Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Tests CategoryProcessUrlRewriteMovingObserver class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryProcessUrlRewriteMovingObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CategoryUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryUrlRewriteGeneratorMock;

    /**
     * @var UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlPersist;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var UrlRewriteHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlRewriteHandler;

    /**
     * @var UrlRewriteBunchReplacer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlRewriteBunchReplacerMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CategoryProcessUrlRewriteMovingObserver
     */
    private $observerModel;

    protected function setUp()
    {
        $this->categoryUrlRewriteGeneratorMock = $this->getMock(
            CategoryUrlRewriteGenerator::class,
            [
                'generate'
            ],
            [],
            '',
            false
        );
        $this->urlPersist = $this->getMock(UrlPersistInterface::class, [], [], '', false);
        $this->urlRewriteHandler = $this->getMock(
            UrlRewriteHandler::class,
            [
                'generateProductUrlRewrites',
                'deleteCategoryRewritesForChildren',
            ],
            [],
            '',
            false
        );

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->setMethods(['isSetFlag'])
            ->getMockForAbstractClass();

        $this->urlRewriteBunchReplacerMock = $this->getMock(
            UrlRewriteBunchReplacer::class,
            ['doBunchReplace'],
            [],
            '',
            false
        );

        $this->objectManager = new ObjectManager($this);
        $this->observerModel = $this->objectManager->getObject(
            CategoryProcessUrlRewriteMovingObserver::class,
            [
                'categoryUrlRewriteGenerator' => $this->categoryUrlRewriteGeneratorMock,
                'urlPersist' => $this->urlPersist,
                'scopeConfig' => $this->scopeConfig,
                'urlRewriteHandler' => $this->urlRewriteHandler
            ]
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->observerModel,
            'urlRewriteBunchReplacer',
            $this->urlRewriteBunchReplacerMock
        );
    }

    /**
     * Covers execite() method.
     *
     * @dataProvider testExecuteDataProvider
     * @return void
     */
    public function testExecute($changedParent, $saveRewritesHistory)
    {
        $storeId = 1;
        $category = $this->getMock(
            \Magento\Catalog\Model\Category::class,
            [
                'getStoreId',
                'dataHasChangedFor'
            ],
            [],
            '',
            false
        );
        $category->expects($this->once())->method('dataHasChangedFor')->with('parent_id')->willReturn($changedParent);

        if ($changedParent) {
            $this->getMockData($saveRewritesHistory, $category, $storeId);
        }

        $event = $this->getMock(\Magento\Framework\Event::class, ['getCategory'], [], '', false);
        $event->expects($this->any())->method('getCategory')->willReturn($category);

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
     * Data provider for testExecute()
     *
     * @return array
     */
    public function testExecuteDataProvider()
    {
        return [
            1 => [
                'changedParent' => true,
                'saveRewritesHistory' => true
            ],
            2 => [
                'changedParent' =>false,
                'saveRewritesHistory' => true
            ],
        ];
    }

    /**
     * Returns Mock data for test.
     *
     * @param $saveRewritesHistory
     * @param $category
     * @param $storeId
     */
    private function getMockData($saveRewritesHistory, $category, $storeId)
    {
        $category->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                UrlKeyRenderer::XML_PATH_SEO_SAVE_HISTORY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )->willReturn($saveRewritesHistory);

        $this->categoryUrlRewriteGeneratorMock
            ->expects($this->once())
            ->method('generate')
            ->with($category, true)
            ->willReturn($this->getCategoryUrlRewritesGenerated());

        $this->urlRewriteHandler
            ->expects($this->once())
            ->method('generateProductUrlRewrites')
            ->with($category)
            ->willReturn($this->getProductUrlRewritesGenerated());
        $this->urlRewriteHandler
            ->expects($this->once())
            ->method('deleteCategoryRewritesForChildren')
            ->with($category)
            ->willReturn(null);

        $this->urlRewriteBunchReplacerMock->expects($this->once())
            ->method('doBunchReplace');
    }

    /**
     * Returns an array of generated UrlRewrites for category.
     *
     * @return array
     */
    private function getCategoryUrlRewritesGenerated()
    {
        $categoryUrlRewriteGenerated1 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $categoryUrlRewriteGenerated1->setRequestPath('category1/category2.html')
            ->setStoreId(1)
            ->setEntityType('category')
            ->setEntityId(6)
            ->setTargetPath('catalog/category/view/id/6');
        $categoryUrlRewriteGenerated2 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $categoryUrlRewriteGenerated2->setRequestPath('category2.html')
            ->setStoreId(1)
            ->setEntityType('category')
            ->setEntityId(6)
            ->setTargetPath('category1/category2.html')
            ->setRedirectType(301)
            ->setIsAutogenerated(0);

        $categoryUrlRewriteGenerated = [
            'category1/category2.html_1' => $categoryUrlRewriteGenerated1,
            'category2.html_1' => $categoryUrlRewriteGenerated2
        ];

        return $categoryUrlRewriteGenerated;
    }

    /**
     * Returns an array of generated UrlRewrites for products.
     *
     * @return array
     */
    private function getProductUrlRewritesGenerated()
    {
        $productUrlRewriteGenerated1 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $productUrlRewriteGenerated1->setEntityType('product')
            ->setEntityId(1)
            ->setRequestPath('simple1.html')
            ->setTargetPath('catalog/product/view/id/1')
            ->setStoreId(1);
        $productUrlRewriteGenerated2 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $productUrlRewriteGenerated2->setEntityType('product')
            ->setEntityId(1)
            ->setRequestPath('category1/category2/simple1.html')
            ->setTargetPath('catalog/product/view/id/1/category/6')
            ->setStoreId(1)
            ->setMetadata('a:1:{s:11:"category_id";s:1:"6";}');
        $productUrlRewriteGenerated3 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $productUrlRewriteGenerated3->setEntityType('product')
            ->setEntityId(1)
            ->setRequestPath('category2/simple1.html')
            ->setTargetPath('category1/category2/simple1.html')
            ->setRedirectType(301)
            ->setStoreId(1)
            ->setDescription(null)
            ->setIsAutogenerated(0)
            ->setMetadata('a:1:{s:11:"category_id";s:1:"6";}');
        $productUrlRewriteGenerated4 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $productUrlRewriteGenerated4->setEntityType('product')
            ->setEntityId(1)
            ->setRequestPath('category1/simple1.html')
            ->setTargetPath('catalog/product/view/id/1/category/5')
            ->setStoreId(1)
            ->setMetadata('a:1:{s:11:"category_id";s:1:"5";}');
        $productUrlRewriteGenerated5 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $productUrlRewriteGenerated5->setEntityType('product')
            ->setEntityId(2)
            ->setRequestPath('simple2.html')
            ->setTargetPath('catalog/product/view/id/2')
            ->setStoreId(1);
        $productUrlRewriteGenerated6 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $productUrlRewriteGenerated6->setEntityType('product')
            ->setEntityId(2)
            ->setRequestPath('category1/category2/simple2.html')
            ->setTargetPath('catalog/product/view/id/2/category/6')
            ->setStoreId(1)
            ->setMetadata('a:1:{s:11:"category_id";s:1:"6";}');
        $productUrlRewriteGenerated7 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $productUrlRewriteGenerated7->setEntityType('product')
            ->setEntityId(2)
            ->setRequestPath('category2/simple2.html')
            ->setTargetPath('category1/category2/simple2.html')
            ->setRedirectType(301)
            ->setStoreId(1)
            ->setDescription(null)
            ->setIsAutogenerated(0)
            ->setMetadata('a:1:{s:11:"category_id";s:1:"6";}');

        $productUrlRewriteGenerated8 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $productUrlRewriteGenerated8->setEntityType('product')
            ->setEntityId(2)
            ->setRequestPath('category1/simple2.html')
            ->setTargetPath('catalog/product/view/id/2/category/5')
            ->setStoreId(1)
            ->setMetadata('a:1:{s:11:"category_id";s:1:"6";}');

        $productUrlRewriteGenerated = [
            'simple1.html_1' => $productUrlRewriteGenerated1,
            'category1/category2/simple1.html_1' => $productUrlRewriteGenerated2,
            'category2/simple1.html_1' => $productUrlRewriteGenerated3,
            'category1/simple1.html_1' => $productUrlRewriteGenerated4,
            'simple2.html_1' => $productUrlRewriteGenerated5,
            'category1/category2/simple2.html_1' => $productUrlRewriteGenerated6,
            'category2/simple2.html_1' => $productUrlRewriteGenerated7,
            'category1/simple2.html_1' => $productUrlRewriteGenerated8
        ];

        return $productUrlRewriteGenerated;
    }
}
