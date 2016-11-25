<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteSavingObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\UrlRewrite\Model\UrlPersistInterface;

/**
 * Class ProductProcessUrlRewriteSavingObserver
 */
class ProductProcessUrlRewriteSavingObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductProcessUrlRewriteSavingObserver
     */
    protected $productProcessUrlRewriteSavingObserver;

    /**
     * @var UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlPersist;

    /**
     * @var Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observer;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var ProductUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productUrlRewriteGenerator;

    /**
     * @SuppressWarnings(PHPMD.TooManyFields)
     */
    public function setUp()
    {
        $this->urlPersist = $this->getMockBuilder(UrlPersistInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productUrlRewriteGenerator = $this->getMockBuilder(ProductUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->productProcessUrlRewriteSavingObserver = $objectManagerHelper->getObject(
            ProductProcessUrlRewriteSavingObserver::class,
            [
                'productUrlRewriteGenerator' => $this->productUrlRewriteGenerator,
                'urlPersist' => $this->urlPersist,
            ]
        );

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getProductCategories'
            ])
            ->getMock();

        $event = $this->getMock(Event::class, ['getProduct'], [], '', false);
        $event->method('getProduct')->willReturn($this->product);

        $this->observer = $this->getMock(Observer::class, ['getEvent'], [], '', false);
        $this->observer->method('getEvent')->willReturn($event);

    }

    /**
     * @dataProvider visibilityProvider
     * @param integer $before
     * @param integer $after
     */
    public function testVisibility($before, $after)
    {
        $this->product->setOrigData('visibility', $before);
        $this->product->setData('visibility', $after);

        $this->productUrlRewriteGenerator->expects(static::once())
            ->method('generate')
            ->with($this->product)
            ->willReturn(['test']);
        $this->urlPersist->expects(static::once())
            ->method('replace')
            ->with(['test']);

        $this->productProcessUrlRewriteSavingObserver->execute($this->observer);
    }

    public function visibilityProvider()
    {
        return [
            ['origData' => Visibility::VISIBILITY_NOT_VISIBLE, 'data' => Visibility::VISIBILITY_IN_CATALOG],
            ['origData' => null, 'data' => Visibility::VISIBILITY_IN_CATALOG],
            ['origData' => Visibility::VISIBILITY_BOTH, 'data' => Visibility::VISIBILITY_IN_SEARCH],
        ];
    }

    /**
     * @dataProvider notVisibilityProvider
     * @param integer $before
     * @param integer $after
     */
    public function testNotVisibility($before, $after)
    {
        $this->product->setOrigData('visibility', $before);
        $this->product->setData('visibility', $after);

        $this->productUrlRewriteGenerator->expects(static::never())->method('generate');
        $this->urlPersist->expects(static::never())->method('replace');

        $this->productProcessUrlRewriteSavingObserver->execute($this->observer);
    }

    public function notVisibilityProvider()
    {
        return [
            ['origData' => Visibility::VISIBILITY_IN_SEARCH, 'data' => Visibility::VISIBILITY_IN_SEARCH],
            ['origData' => Visibility::VISIBILITY_IN_CATALOG, 'data' => Visibility::VISIBILITY_NOT_VISIBLE],
            ['origData' => null, 'data' => Visibility::VISIBILITY_NOT_VISIBLE],
        ];
    }
}
