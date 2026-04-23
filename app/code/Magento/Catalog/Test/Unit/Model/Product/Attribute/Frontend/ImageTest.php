<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Frontend;

use PHPUnit\Framework\Attributes\DataProvider;
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
     * @param string $expectedImage
     * @param string $productImage
     */
    #[DataProvider('getUrlDataProvider')]
    public function testGetUrl(string $expectedImage, string $productImage)
    {
        $this->assertEquals($expectedImage, $this->model->getUrl($this->getMockedProduct($productImage)));
    }

    /**
     * Data provider for testGetUrl
     *
     * @return array
     */
    public static function getUrlDataProvider(): array
    {
        return [
            ['catalog/product/img.jpg', 'img.jpg'],
            ['catalog/product/img.jpg', '/img.jpg'],
        ];
    }

    protected function setUp(): void
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
        $mock = $this->createPartialMock(Product::class, ['getData', 'getStore']);

        $mock->method('getData')->willReturn($productImage);

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

        $mock = $this->createStub(StoreManagerInterface::class);
        $mock->method('getStore')->willReturn($mockedStore);

        return $mock;
    }

    /**
     * @return Store
     */
    private function getMockedStore(): Store
    {
        $mock = $this->createPartialMock(Store::class, ['getBaseUrl']);

        $mock->method('getBaseUrl')->willReturn('');

        return $mock;
    }

    /**
     * @return AbstractAttribute
     */
    private function getMockedAttribute(): AbstractAttribute
    {
        $mock = $this->createPartialMock(AbstractAttribute::class, ['getAttributeCode']);

        $mock->expects($this->any())
            ->method('getAttributeCode');

        return $mock;
    }
}
