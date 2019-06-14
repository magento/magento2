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
                'isVisibleInSiteVisibility',
                'getIsChangedWebsites',
                'getIsChangedCategories',
                'getStoreId'
            ]);
        $this->product->expects($this->any())->method('getId')->will($this->returnValue(3));
        $this->event = $this->createPartialMock(\Magento\Framework\Event::class, ['getProduct']);
        $this->event->expects($this->any())->method('getProduct')->willReturn($this->product);
        $this->observer = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEvent']);
        $this->observer->expects($this->any())->method('getEvent')->willReturn($this->event);
        $this->productUrlRewriteGenerator = $this->createPartialMock(
            \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::class,
            ['generate']
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
    public function urlKeyDataProvider()
    {
        return [
            'url changed' => [
                'isChangedUrlKey'       => true,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'expectedReplaceCount'  => 1,

            ],
            'no changes' => [
                'isChangedUrlKey'       => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'expectedReplaceCount'  => 0
            ],
            'websites changed' => [
                'isChangedUrlKey'       => false,
                'isChangedWebsites'     => true,
                'isChangedCategories'   => false,
                'expectedReplaceCount'  => 1
            ],
            'categories changed' => [
                'isChangedUrlKey'       => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => true,
                'expectedReplaceCount'  => 1
            ]
        ];
    }

    /**
     * @param bool $isChangedUrlKey
     * @param bool $isChangedWebsites
     * @param bool $isChangedCategories
     * @param int $expectedReplaceCount
     *
     * @dataProvider urlKeyDataProvider
     */
    public function testExecuteUrlKey(
        $isChangedUrlKey,
        $isChangedWebsites,
        $isChangedCategories,
        $expectedReplaceCount
    ) {
        $this->product->expects($this->any())->method('getStoreId')->will($this->returnValue(12));

        $this->product->expects($this->any())
            ->method('dataHasChangedFor')
            ->will($this->returnValueMap(
                [
                    ['url_key', $isChangedUrlKey]
                ]
            ));

        $this->product->expects($this->any())
            ->method('getIsChangedWebsites')
            ->will($this->returnValue($isChangedWebsites));

        $this->product->expects($this->any())
            ->method('getIsChangedCategories')
            ->will($this->returnValue($isChangedCategories));

        $this->urlPersist->expects($this->exactly($expectedReplaceCount))
            ->method('replace')
            ->with([3 => 'rewrite']);

        $this->model->execute($this->observer);
    }
}
