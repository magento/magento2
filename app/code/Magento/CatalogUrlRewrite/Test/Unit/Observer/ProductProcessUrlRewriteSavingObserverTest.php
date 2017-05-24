<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Store\Model\Store;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class ProductProcessUrlRewriteSavingObserverTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductProcessUrlRewriteSavingObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlPersist;

    /**
     * @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observer;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteSavingObserver
     */
    protected $model;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->urlPersist = $this->getMock(\Magento\UrlRewrite\Model\UrlPersistInterface::class, [], [], '', false);
        $this->product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getId',
                'dataHasChangedFor',
                'isVisibleInSiteVisibility',
                'getIsChangedWebsites',
                'getIsChangedCategories',
                'getStoreId'
            ],
            [],
            '',
            false
        );
        $this->product->expects($this->any())->method('getId')->will($this->returnValue(3));
        $this->event = $this->getMock(\Magento\Framework\Event::class, ['getProduct'], [], '', false);
        $this->event->expects($this->any())->method('getProduct')->willReturn($this->product);
        $this->observer = $this->getMock(\Magento\Framework\Event\Observer::class, ['getEvent'], [], '', false);
        $this->observer->expects($this->any())->method('getEvent')->willReturn($this->event);
        $this->productUrlRewriteGenerator = $this->getMock(
            \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::class,
            ['generate'],
            [],
            '',
            false
        );
        $this->productUrlRewriteGenerator->expects($this->any())
            ->method('generate')
            ->will($this->returnValue([3 => 'rewrite']));
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            \Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteSavingObserver::class,
            [
                'productUrlRewriteGenerator' => $this->productUrlRewriteGenerator,
                'urlPersist' => $this->urlPersist
            ]
        );
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function testUrlKeyDataProvider()
    {
        return [
            'url changed' => [
                'isChangedUrlKey'       => true,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => true,
                'expectedDeleteCount'   => 1,
                'expectedReplaceCount'  => 1,

            ],
            'no chnages' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => true,
                'expectedDeleteCount'   => 0,
                'expectedReplaceCount'  => 0
            ],
            'visibility changed' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => true,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => true,
                'expectedDeleteCount'   => 1,
                'expectedReplaceCount'  => 1
            ],
            'websites changed' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => true,
                'isChangedCategories'   => false,
                'visibilityResult'      => true,
                'expectedDeleteCount'   => 1,
                'expectedReplaceCount'  => 1
            ],
            'categories changed' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => true,
                'visibilityResult'      => true,
                'expectedDeleteCount'   => 1,
                'expectedReplaceCount'  => 1
            ],
            'url changed invisible' => [
                'isChangedUrlKey'       => true,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => false,
                'expectedDeleteCount'   => 1,
                'expectedReplaceCount'  => 0
            ],
        ];
    }

    /**
     * @param bool $isChangedUrlKey
     * @param bool $isChangedVisibility
     * @param bool $isChangedWebsites
     * @param bool $isChangedCategories
     * @param bool $visibilityResult
     * @param int $expectedDeleteCount
     * @param int $expectedReplaceCount
     *
     * @dataProvider testUrlKeyDataProvider
     */
    public function testExecuteUrlKey(
        $isChangedUrlKey,
        $isChangedVisibility,
        $isChangedWebsites,
        $isChangedCategories,
        $visibilityResult,
        $expectedDeleteCount,
        $expectedReplaceCount
    ) {
        $this->product->expects($this->any())->method('getStoreId')->will($this->returnValue(12));

        $this->product->expects($this->any())
            ->method('dataHasChangedFor')
            ->will($this->returnValueMap(
                [
                    ['visibility', $isChangedVisibility],
                    ['url_key', $isChangedUrlKey]
                ]
            ));

        $this->product->expects($this->any())
            ->method('getIsChangedWebsites')
            ->will($this->returnValue($isChangedWebsites));

        $this->product->expects($this->any())
            ->method('getIsChangedCategories')
            ->will($this->returnValue($isChangedCategories));

        $this->urlPersist->expects($this->exactly($expectedDeleteCount))->method('deleteByData')->with([
            UrlRewrite::ENTITY_ID => $this->product->getId(),
            UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::REDIRECT_TYPE => 0,
            UrlRewrite::STORE_ID => $this->product->getStoreId()
        ]);

        $this->product->expects($this->any())
            ->method('isVisibleInSiteVisibility')
            ->will($this->returnValue($visibilityResult));

        $this->urlPersist->expects($this->exactly($expectedReplaceCount))
            ->method('replace')
            ->with([3 => 'rewrite']);

        $this->model->execute($this->observer);
    }
}
