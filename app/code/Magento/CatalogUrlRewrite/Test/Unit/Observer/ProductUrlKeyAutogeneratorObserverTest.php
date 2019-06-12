<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
    protected function setUp()
=======
    protected function setUp(): void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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

<<<<<<< HEAD
    public function testExecuteWithUrlKey()
=======
    /**
     * @return void
     */
    public function testExecuteWithUrlKey(): void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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

<<<<<<< HEAD
    public function testExecuteWithEmptyUrlKey()
=======
    /**
     * @return void
     */
    public function testExecuteWithEmptyUrlKey(): void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
