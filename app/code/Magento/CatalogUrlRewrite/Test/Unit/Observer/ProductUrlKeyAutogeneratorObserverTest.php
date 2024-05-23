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
use Magento\Backend\Model\Validator\UrlKey\CompositeUrlKey;

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
     * @var CompositeUrlKey|MockObject
     */
    private $compositeUrlValidator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->productUrlPathGenerator = $this->getMockBuilder(ProductUrlPathGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrlKey'])
            ->getMock();

        $this->compositeUrlValidator = $this->getMockBuilder(CompositeUrlKey::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate'])
            ->getMock();

        $this->productUrlKeyAutogeneratorObserver = (new ObjectManagerHelper($this))->getObject(
            ProductUrlKeyAutogeneratorObserver::class,
            [
                'productUrlPathGenerator' => $this->productUrlPathGenerator,
                'compositeUrlValidator' => $this->compositeUrlValidator
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
            ->addMethods(['setUrlKey'])
            ->getMock();
        $product->expects($this->atLeastOnce())->method('setUrlKey')->with($urlKey);
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getProduct'])
            ->getMock();
        $event->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        /** @var Observer|MockObject $observer */
        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEvent'])
            ->getMock();
        $observer->expects($this->atLeastOnce())->method('getEvent')->willReturn($event);
        $this->productUrlPathGenerator->expects($this->atLeastOnce())->method('getUrlKey')->with($product)
            ->willReturn($urlKey);

        $this->compositeUrlValidator->expects($this->once())->method('validate')->with($urlKey)->willReturn([]);

        $this->productUrlKeyAutogeneratorObserver->execute($observer);
    }

    /**
     * @return void
     */
    public function testExecuteWithEmptyUrlKey(): void
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['setUrlKey'])
            ->getMock();
        $product->expects($this->never())->method('setUrlKey');
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getProduct'])
            ->getMock();
        $event->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        /** @var Observer|MockObject $observer */
        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEvent'])
            ->getMock();
        $observer->expects($this->atLeastOnce())->method('getEvent')->willReturn($event);
        $this->productUrlPathGenerator->expects($this->atLeastOnce())->method('getUrlKey')->with($product)
            ->willReturn(null);

        $this->productUrlKeyAutogeneratorObserver->execute($observer);
    }
}
