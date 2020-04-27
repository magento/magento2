<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for GetContent
 */
class GetContentTest extends TestCase
{
    /**
     * @var GetContent
     */
    private $getContent;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getContent = $objectManager->get(GetContent::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->metadataPool = $objectManager->get(MetadataPool::class);
    }

    /**
     * Test for get content from product in different store views
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testProduct(): void
    {
        $product = $this->productRepository->get('simple');
        $id = (int) $product->getData($this->metadataPool->getMetadata(ProductInterface::class)->getLinkField());
        $this->assertEquals(
            'Description with <b>html tag</b>',
            $this->getContent->execute($id, $product->getAttributes()['description'])
        );
    }

    /**
     * Test for get content from product in different store views
     *
     * @magentoDataFixture Magento/Catalog/_files/product_multiwebsite_different_description.php
     */
    public function testProductTwoWebsites(): void
    {
        $product = $this->productRepository->get('simple-on-two-websites-different-description');
        $id = (int) $product->getData($this->metadataPool->getMetadata(ProductInterface::class)->getLinkField());
        $this->assertEquals(
            '<p>Product base description</p>' . PHP_EOL . '<p>Product second description</p>',
            $this->getContent->execute($id, $product->getAttributes()['description'])
        );
    }
}
