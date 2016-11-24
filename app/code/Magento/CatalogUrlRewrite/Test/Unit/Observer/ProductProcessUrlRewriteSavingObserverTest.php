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
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

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

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->productProcessUrlRewriteSavingObserver = $this->objectManagerHelper->getObject(
            ProductProcessUrlRewriteSavingObserver::class,
            [
                'productUrlRewriteGenerator' => $this->productUrlRewriteGenerator,
                'urlPersist' => $this->urlPersist,
            ]
        );

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getProductCategories',
                'dataHasChangedFor',
                'getVisibility'
            ])
            ->getMock();

        $this->event = $this->getMock(Event::class, ['getProduct'], [], '', false);
        $this->event->method('getProduct')->willReturn($this->product);

        $this->observer = $this->getMock(Observer::class, ['getEvent'], [], '', false);
        $this->observer->method('getEvent')->willReturn($this->event);

    }

    public function testVisibility()
    {
        $this->productUrlRewriteGenerator->expects(static::once())
            ->method('generate')
            ->with($this->product)
            ->willReturn([]);
        $this->urlPersist->expects(static::atLeastOnce())
            ->method('replace')
            ->with([]);
        $this->product->expects(static::atLeastOnce())
            ->method('getVisibility')
            ->willReturn(Visibility::VISIBILITY_IN_CATALOG);
        $this->product->expects(static::atLeastOnce())
            ->method('dataHasChangedFor')
            ->willReturnMap([
                ['visibility', true],
            ]);

        $this->productProcessUrlRewriteSavingObserver->execute($this->observer);
    }
}
