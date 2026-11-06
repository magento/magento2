<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Mview\View;
use Magento\Framework\ObjectManagerInterface;
use Magento\Indexer\Model\Mview\View\State;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Every indexer needs its own mview view and state: they carry the view id and version.
 */
class MviewInstanceIsolationTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testEachViewGetsItsOwnState(): void
    {
        $first = $this->objectManager->create(View::class);
        $second = $this->objectManager->create(View::class);

        $this->assertInstanceOf(State::class, $first->getState());
        $this->assertNotSame($first->getState(), $second->getState());
    }

    public function testIndexersDoNotShareViewInstances(): void
    {
        $registry = $this->objectManager->get(IndexerRegistry::class);
        $categoryProduct = $registry->get('catalog_category_product');
        $productPrice = $registry->get('catalog_product_price');

        $categoryProductView = $categoryProduct->getView();
        $productPriceView = $productPrice->getView();

        $this->assertNotSame($categoryProductView, $productPriceView);
        $this->assertSame('catalog_category_product', $categoryProductView->getId());
        $this->assertSame('catalog_product_price', $productPriceView->getId());
        $this->assertNotSame($categoryProductView->getState(), $productPriceView->getState());
        $this->assertSame('catalog_category_product', $categoryProductView->getState()->getViewId());
        $this->assertSame('catalog_product_price', $productPriceView->getState()->getViewId());
    }
}
