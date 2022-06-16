<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Test\Unit\Plugin\Cms\Model\Store;

use Magento\Cms\Api\Data\PageSearchResultsInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\CmsUrlRewrite\Plugin\Cms\Model\Store\View;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ResourceModel\Store;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CmsUrlRewrite\Plugin\Cms\Model\Store\View.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends TestCase
{
    private const STUB_STORE_ID = 777;
    private const STUB_URL_REWRITE = ['cms/page/view'];

    /**
     * @var View
     */
    private $model;

    /**
     * @var SearchCriteria|MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var PageSearchResultsInterface|MockObject
     */
    private $pageSearchResultMock;

    /**
     * @var Page|MockObject
     */
    private $pageMock;

    /**
     * @var Store|MockObject
     */
    private $storeObjectMock;

    /**
     * @var AbstractModel|MockObject
     */
    private $abstractModelMock;

    /**
     * @var UrlPersistInterface|MockObject
     */
    private $urlPersistMock;

    /**
     * @var PageRepositoryInterface|MockObject
     */
    private $pageRepositoryMock;

    /**
     * @var CmsPageUrlRewriteGenerator|MockObject
     */
    private $cmsPageUrlGeneratorMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->storeObjectMock = $this->createMock(Store::class);
        $this->searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->pageSearchResultMock = $this->createMock(PageSearchResultsInterface::class);

        $this->pageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->addMethods(['setStoreId'])
            ->getMock();

        $this->abstractModelMock = $this->getMockBuilder(AbstractModel::class)
            ->onlyMethods(['isObjectNew', 'getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->urlPersistMock = $this->createMock(UrlPersistInterface::class);
        $this->pageRepositoryMock = $this->createMock(PageRepositoryInterface::class);
        $this->cmsPageUrlGeneratorMock = $this->createMock(CmsPageUrlRewriteGenerator::class);

        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->expects($this->any())
            ->method('addFilter')
            ->willReturnSelf();
        $searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->model = $objectManager->getObject(
            View::class,
            [
                'urlPersist' => $this->urlPersistMock,
                'searchCriteriaBuilder' => $searchCriteriaBuilderMock,
                'pageRepository' => $this->pageRepositoryMock,
                'cmsPageUrlRewriteGenerator' => $this->cmsPageUrlGeneratorMock,
            ]
        );
    }

    /**
     * After save when object is not new
     *
     * @return void
     */
    public function testAfterSaveObjectIsNotNew(): void
    {
        $storeResult = clone $this->storeObjectMock;

        $this->abstractModelMock->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(false);

        $this->urlPersistMock->expects($this->never())
            ->method('replace');

        $result = $this->model->afterSave($this->storeObjectMock, $storeResult, $this->abstractModelMock);
        $this->assertEquals($storeResult, $result);
    }

    /**
     * After save when object is new
     *
     * @return void
     */
    public function testAfterSaveObjectIsNew(): void
    {
        $storeResult = clone $this->storeObjectMock;

        $this->abstractModelMock->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(true);
        $this->abstractModelMock->expects($this->once())
            ->method('getId')
            ->willReturn(self::STUB_STORE_ID);
        $this->pageRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->pageSearchResultMock);
        $this->pageSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->pageMock]);
        $this->pageMock->expects($this->once())
            ->method('setStoreId')
            ->with(self::STUB_STORE_ID);
        $this->cmsPageUrlGeneratorMock->expects($this->once())
            ->method('generate')
            ->with($this->pageMock)
            ->willReturn(self::STUB_URL_REWRITE);
        $this->urlPersistMock->expects($this->once())
            ->method('replace')
            ->with(self::STUB_URL_REWRITE);

        $result = $this->model->afterSave($this->storeObjectMock, $storeResult, $this->abstractModelMock);
        $this->assertEquals($storeResult, $result);
    }
}
