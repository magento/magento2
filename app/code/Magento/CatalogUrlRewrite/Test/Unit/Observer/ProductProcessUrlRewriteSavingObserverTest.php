<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class ProductProcessUrlRewriteSavingObserverTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductProcessUrlRewriteSavingObserverTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Catalog\Model\ResourceModel\Product | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceProduct;
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
        $this->urlPersist = $this->createMock(\Magento\UrlRewrite\Model\UrlPersistInterface::class);
        $this->product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, [
                'getId',
                'dataHasChangedFor',
                'getVisibleInSiteVisibilities',
                'getIsChangedWebsites',
                'getIsChangedCategories',
                'getStoreId',
                'getStoreIds',
                'getResource'
                
            ]);
        $this->resourceProduct = $this->createPartialMock(\Magento\Catalog\Model\ResourceModel\Product::class, [
            'getAttributeRawValue'
        ]);
        $this->product->expects($this->any())->method('getId')->will($this->returnValue(3));
        $this->product->expects($this->any())->method('getResource')->willReturn($this->resourceProduct);
        $this->event = $this->createPartialMock(\Magento\Framework\Event::class, ['getProduct']);
        $this->event->expects($this->any())->method('getProduct')->willReturn($this->product);
        $this->observer = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEvent']);
        $this->observer->expects($this->any())->method('getEvent')->willReturn($this->event);
        $this->productUrlRewriteGenerator = $this->createPartialMock(
            \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::class,
            ['generate']
        );
        $this->product->expects($this->any())
            ->method('getVisibleInSiteVisibilities')
            ->will($this->returnValue([2,3,4]));

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
    public function urlKeyDataProvider()
    {
        return [
            'url changed' => [
                'isChangedUrlKey'       => true,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => 3,
                'expectedDeleteCount'   => 1,
                'expectedReplaceCount'  => 1,
                'global'                => false

            ],
            'no chnages' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => 3,
                'expectedDeleteCount'   => 0,
                'expectedReplaceCount'  => 0,
                'global'                => false,
            ],
            'visibility changed' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => true,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => 3,
                'expectedDeleteCount'   => 1,
                'expectedReplaceCount'  => 1,
                'global'                => false
            ],
            'websites changed' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => true,
                'isChangedCategories'   => false,
                'visibilityResult'      => 3,
                'expectedDeleteCount'   => 1,
                'expectedReplaceCount'  => 2,
                'global'                => true
            ],
            'categories changed' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => true,
                'visibilityResult'      => 3,
                'expectedDeleteCount'   => 1,
                'expectedReplaceCount'  => 2,
                'global'                => true
            ],
            'url changed invisible' => [
                'isChangedUrlKey'       => true,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => 0,
                'expectedDeleteCount'   => 1,
                'expectedReplaceCount'  => 0,
                'global'                => false
            ],
        ];
    }

    /**
     * @param bool $isChangedUrlKey
     * @param bool $isChangedVisibility
     * @param bool $isChangedWebsites
     * @param bool $isChangedCategories
     * @param int $visibilityResult
     * @param int $expectedDeleteCount
     * @param int $expectedReplaceCount
     * @param bool $global
     *
     * @dataProvider urlKeyDataProvider
     */
    public function testExecuteUrlKey(
        $isChangedUrlKey,
        $isChangedVisibility,
        $isChangedWebsites,
        $isChangedCategories,
        $visibilityResult,
        $expectedDeleteCount,
        $expectedReplaceCount,
        $global
    ) {
        $this->product->expects($this->any())->method('getStoreId')->will($this->returnValue(12));
        $this->product->expects($this->any())->method('getStoreIds')->will($this->returnValue([12,13]));

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

        $params = [
            UrlRewrite::ENTITY_ID => $this->product->getId(),
            UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::REDIRECT_TYPE => 0,
        ];
        if (!$global) {
            $params[UrlRewrite::STORE_ID] = $this->product->getStoreId();
        }
        $this->urlPersist->expects($this->exactly($expectedDeleteCount))->method('deleteByData')->with($params);
        $this->resourceProduct->expects($this->any())->method('getAttributeRawValue')->will($this->returnValue($visibilityResult));


        $this->urlPersist->expects($this->exactly($expectedReplaceCount))
            ->method('replace')
            ->with([3 => 'rewrite']);

        $this->model->execute($this->observer);
    }
}
