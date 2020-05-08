<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Observer\ProductUrlKeyAutogeneratorObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\CatalogUrlRewrite\Observer\ProductUrlKeyAutogeneratorObserver class
 */
class ProductUrlKeyAutogeneratorObserverTest extends TestCase
{
    /**
     * @var ProductUrlPathGenerator|MockObject
     */
    private $productUrlPathGenerator;

    /** @var ProductUrlKeyAutogeneratorObserver */
    private $productUrlKeyAutogeneratorObserver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->productUrlPathGenerator = $this->getMockBuilder(ProductUrlPathGenerator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrlKey'])
            ->getMock();

        $this->productUrlKeyAutogeneratorObserver = (new ObjectManagerHelper($this))->getObject(
            ProductUrlKeyAutogeneratorObserver::class,
            [
                'productUrlPathGenerator' => $this->productUrlPathGenerator
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteWithUrlKey(): void
    {
        $urlKey = 'product_url_key';

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['setUrlKey'])
            ->getMock();
        $product->expects($this->atLeastOnce())->method('setUrlKey')->with($urlKey);
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();
        $event->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        /** @var Observer|MockObject $observer */
        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();
        $observer->expects($this->atLeastOnce())->method('getEvent')->willReturn($event);
        $this->productUrlPathGenerator->expects($this->atLeastOnce())->method('getUrlKey')->with($product)
            ->willReturn($urlKey);

        $this->productUrlKeyAutogeneratorObserver->execute($observer);
    }

    /**
     * @return void
     */
    public function testExecuteWithEmptyUrlKey(): void
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['setUrlKey'])
            ->getMock();
        $product->expects($this->never())->method('setUrlKey');
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();
        $event->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        /** @var Observer|MockObject $observer */
        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();
        $observer->expects($this->atLeastOnce())->method('getEvent')->willReturn($event);
        $this->productUrlPathGenerator->expects($this->atLeastOnce())->method('getUrlKey')->with($product)
            ->willReturn(null);

        $this->productUrlKeyAutogeneratorObserver->execute($observer);
    }
}
