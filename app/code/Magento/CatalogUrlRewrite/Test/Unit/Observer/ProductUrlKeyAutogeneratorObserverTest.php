<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;

/**
 * Unit tests for \Magento\CatalogUrlRewrite\Observer\ProductUrlKeyAutogeneratorObserver class
 */
class ProductUrlKeyAutogeneratorObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productUrlPathGenerator;

    /** @var \Magento\CatalogUrlRewrite\Observer\ProductUrlKeyAutogeneratorObserver */
    private $productUrlKeyAutogeneratorObserver;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productUrlPathGenerator = $this->getMockBuilder(ProductUrlPathGenerator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrlKey'])
            ->getMock();

        $this->productUrlKeyAutogeneratorObserver = (new ObjectManagerHelper($this))->getObject(
            \Magento\CatalogUrlRewrite\Observer\ProductUrlKeyAutogeneratorObserver::class,
            [
                'productUrlPathGenerator' => $this->productUrlPathGenerator
            ]
        );
    }

    public function testExecuteWithUrlKey()
    {
        $urlKey = 'product_url_key';

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['setUrlKey'])
            ->getMock();
        $product->expects($this->atLeastOnce())->method('setUrlKey')->with($urlKey);
        $event = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();
        $event->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $observer */
        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();
        $observer->expects($this->atLeastOnce())->method('getEvent')->willReturn($event);
        $this->productUrlPathGenerator->expects($this->atLeastOnce())->method('getUrlKey')->with($product)
            ->willReturn($urlKey);

        $this->productUrlKeyAutogeneratorObserver->execute($observer);
    }

    public function testExecuteWithEmptyUrlKey()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['setUrlKey'])
            ->getMock();
        $product->expects($this->never())->method('setUrlKey');
        $event = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();
        $event->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $observer */
        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();
        $observer->expects($this->atLeastOnce())->method('getEvent')->willReturn($event);
        $this->productUrlPathGenerator->expects($this->atLeastOnce())->method('getUrlKey')->with($product)
            ->willReturn(null);

        $this->productUrlKeyAutogeneratorObserver->execute($observer);
    }
}
