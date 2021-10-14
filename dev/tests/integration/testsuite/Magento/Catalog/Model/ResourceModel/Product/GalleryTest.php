<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\ResourceConnection;

/**
 * Test for \Magento\Catalog\Model\ResourceModel\Product\Gallery.
 *
 * @magentoAppArea adminhtml
 */
class GalleryTest extends TestCase
{
    /**
     * @var Gallery
     */
    private $galleryResource;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->productRepository = $objectManager->create(ProductRepositoryInterface::class);
        $this->galleryResource = $objectManager->create(Gallery::class);
        $this->resource = $objectManager->create(ResourceConnection::class);
        $this->readHandler = $objectManager->create(ReadHandler::class);
    }

    /**
     * Verify catalog_product_entity_media_gallery table will not have data after deleting the product
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testDeleteProductWithImage(): void
    {
        $product = $this->productRepository->get('simple');

        $attributeId = $this->readHandler->getAttribute()->getAttributeId();
        $mediaGalleryData = $this->galleryResource->loadProductGalleryByAttributeId($product, $attributeId);
        $values = array_column($mediaGalleryData, 'value_id');
        $this->assertNotEmpty($this->getMediaGalleryDataByValues($values));

        $this->productRepository->delete($product);

        $this->assertEmpty($this->getMediaGalleryDataByValues($values));
    }

    /**
     * Return data from catalog_product_entity_media_gallery_values table
     *
     * @param array $values
     * @return array
     */
    private function getMediaGalleryDataByValues(array $values): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName(Gallery::GALLERY_TABLE))
            ->where('value_id IN (?)', $values);

        return $connection->fetchAll($select);
    }
}
