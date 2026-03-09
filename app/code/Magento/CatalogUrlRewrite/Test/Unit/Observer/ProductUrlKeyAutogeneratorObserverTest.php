<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Backend\Model\Validator\UrlKey\CompositeUrlKey;
use Magento\CatalogUrlRewrite\Observer\ProductUrlKeyAutogeneratorObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\CatalogUrlRewrite\Observer\ProductUrlKeyAutogeneratorObserver class
 */
class ProductUrlKeyAutogeneratorObserverTest extends TestCase
{
    use MockCreationTrait;

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
        $this->productUrlPathGenerator = $this->createPartialMock(
            ProductUrlPathGenerator::class,
            ['getUrlKey']
        );

        $this->compositeUrlValidator = $this->createPartialMock(
            CompositeUrlKey::class,
            ['validate']
        );

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

        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['setUrlKey']
        );
        $product->expects($this->atLeastOnce())->method('setUrlKey')->with($urlKey);
        $event = $this->createPartialMockWithReflection(
            Event::class,
            ['getProduct']
        );
        $event->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        /** @var Observer|MockObject $observer */
        $observer = $this->createPartialMock(
            Observer::class,
            ['getEvent']
        );
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
        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['setUrlKey']
        );
        $product->expects($this->never())->method('setUrlKey');
        $event = $this->createPartialMockWithReflection(
            Event::class,
            ['getProduct']
        );
        $event->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        /** @var Observer|MockObject $observer */
        $observer = $this->createPartialMock(
            Observer::class,
            ['getEvent']
        );
        $observer->expects($this->atLeastOnce())->method('getEvent')->willReturn($event);
        $this->productUrlPathGenerator->expects($this->atLeastOnce())->method('getUrlKey')->with($product)
            ->willReturn(null);

        $this->productUrlKeyAutogeneratorObserver->execute($observer);
    }
}
