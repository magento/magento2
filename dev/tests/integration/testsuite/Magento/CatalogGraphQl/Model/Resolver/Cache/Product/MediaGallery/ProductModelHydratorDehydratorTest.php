<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Cache\Product\MediaGallery;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ProductModelHydratorDehydratorTest extends TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_with_media_gallery.php
     */
    public function testModelHydration(): void
    {
        $productModel = $this->productRepository->get('simple_product_with_media');
        $resolverData = $this->extractResolverData($productModel);
        $originalResolverData = $resolverData;

        /** @var ProductModelDehydrator $dehydrator */
        $dehydrator = $this->objectManager->get(ProductModelDehydrator::class);
        $dehydrator->dehydrate($resolverData);
        $mediaGalleryEntity = $resolverData[0];
        $this->assertArrayNotHasKey('model', $mediaGalleryEntity);
        $this->assertArrayHasKey('model_info', $mediaGalleryEntity);

        $serializedData = $this->serializer->serialize($resolverData);
        $resolverData = $this->serializer->unserialize($serializedData);

        /** @var ProductModelHydrator $hydrator */
        $hydrator = $this->objectManager->get(ProductModelHydrator::class);
        $resolverDataEntityOne = $resolverData[0];
        $hydrator->hydrate($resolverDataEntityOne);
        $hydratedModel = $resolverDataEntityOne['model'];
        $this->assertInstanceOf(ProductInterface::class, $hydratedModel);
        $originalModel = $originalResolverData[0]['model'];
        $this->assertEquals($originalModel->getId(), $hydratedModel->getId());
    }

    /**
     * Extract media gallery resolver data
     *
     * @param ProductInterface $product
     * @return array
     */
    private function extractResolverData(ProductInterface $product)
    {
        $mediaGalleryEntries = [];
        foreach ($product->getMediaGalleryEntries() ?? [] as $key => $entry) {
            $mediaGalleryEntries[$key] = $entry->getData();
            $mediaGalleryEntries[$key]['model'] = $product;
            if ($entry->getExtensionAttributes() && $entry->getExtensionAttributes()->getVideoContent()) {
                $mediaGalleryEntries[$key]['video_content']
                    = $entry->getExtensionAttributes()->getVideoContent()->getData();
            }
        }
        return $mediaGalleryEntries;
    }
}
