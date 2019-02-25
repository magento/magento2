<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ResourceModel\Quote\Item;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection as QuoteItemCollection;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests Magento\Quote\Model\ResourceModel\Quote\Item\Collection.
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Covers case when during quote item collection load product exists in db but not accessible.
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @return void
     */
    public function testLoadCollectionWithNotAccessibleProduct()
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test01', 'reserved_order_id');

        $this->assertCount(1, $quote->getItemsCollection());

        $product = $this->productRepository->get('simple');
        /** @var ProductCollection $productCollection */
        $productCollection = $this->objectManager->create(ProductCollection::class);
        $this->setPropertyValue($productCollection, '_isCollectionLoaded', true);
        /** @var ProductCollectionFactory|\PHPUnit_Framework_MockObject_MockObject $productCollectionFactoryMock */
        $productCollectionFactoryMock = $this->createMock(ProductCollectionFactory::class);
        $productCollectionFactoryMock->expects($this->any())->method('create')->willReturn($productCollection);

        /** @var QuoteItemCollection $quoteItemCollection */
        $quoteItemCollection = $this->objectManager->create(
            QuoteItemCollection::class,
            [
                'productCollectionFactory' => $productCollectionFactoryMock,
            ]
        );

        $quoteItemCollection->setQuote($quote);
        $this->assertCount(1, $quoteItemCollection);
        $item = $quoteItemCollection->getItemByColumnValue('product_id', $product->getId());

        $this->assertNotNull($item);
        $this->assertTrue($item->isDeleted());
    }

    /**
     * Set object non-public property value.
     *
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     * @return void
     */
    private function setPropertyValue($object, string $propertyName, $value)
    {
        $reflectionClass = new \ReflectionClass($object);
        if ($reflectionClass->hasProperty($propertyName)) {
            $reflectionProperty = $reflectionClass->getProperty($propertyName);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($object, $value);
        }
    }
}
