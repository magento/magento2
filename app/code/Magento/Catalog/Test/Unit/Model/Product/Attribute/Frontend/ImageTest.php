<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Frontend;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Frontend\Image;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    /**
     * @var Image
     */
    private $model;

    /**
     * @dataProvider getUrlDataProvider
     * @param string $expectedImage
     * @param string $productImage
     */
    public function testGetUrl(string $expectedImage, string $productImage)
    {
        $this->assertEquals($expectedImage, $this->model->getUrl($this->getMockedProduct($productImage)));
    }

    /**
     * Data provider for testGetUrl
     *
     * @return array
     */
    public function getUrlDataProvider(): array
    {
        return [
            ['catalog/product/img.jpg', 'img.jpg'],
            ['catalog/product/img.jpg', '/img.jpg'],
        ];
    }

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            Image::class,
            ['storeManager' => $this->getMockedStoreManager()]
        );
        $this->model->setAttribute($this->getMockedAttribute());
    }

    /**
     * @param string $productImage
     * @return Product
     */
    private function getMockedProduct(string $productImage): Product
    {
        $mockBuilder = $this->getMockBuilder(Product::class);
        $mock = $mockBuilder->setMethods(['getData', 'getStore', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($productImage));

        $mock->expects($this->any())
            ->method('getStore');

        return $mock;
    }

    /**
     * @return StoreManagerInterface
     */
    private function getMockedStoreManager(): StoreManagerInterface
    {
        $mockedStore = $this->getMockedStore();

        $mockBuilder = $this->getMockBuilder(StoreManagerInterface::class);
        $mock = $mockBuilder->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($mockedStore));

        return $mock;
    }

    /**
     * @return Store
     */
    private function getMockedStore(): Store
    {
        $mockBuilder = $this->getMockBuilder(Store::class);
        $mock = $mockBuilder->setMethods(['getBaseUrl', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('getBaseUrl')
            ->will($this->returnValue(''));

        return $mock;
    }

    /**
     * @return AbstractAttribute
     */
    private function getMockedAttribute(): AbstractAttribute
    {
        $mockBuilder = $this->getMockBuilder(AbstractAttribute::class);
        $mockBuilder->setMethods(['getAttributeCode', '__wakeup']);
        $mockBuilder->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('getAttributeCode');

        return $mock;
    }
}
