<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Catalog\Model\Product\Gallery\ReadHandler.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
 */
class ReadHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\ReadHandler
     */
    protected $readHandler;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->readHandler = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Gallery\ReadHandler::class
        );
    }

    /**
     * @covers \Magento\Catalog\Model\Product\Gallery\ReadHandler::execute
     */
    public function testExecute()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create(
            \Magento\Catalog\Model\Product::class
        );

        /**
         * @var $entityMetadata \Magento\Framework\EntityManager\EntityMetadata
         */
        $entityMetadata = $this->objectManager
            ->get(MetadataPool::class)
            ->getMetadata(ProductInterface::class);
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $linkFieldId = $productRepository->get('simple')->getData($entityMetadata->getLinkField());

        $product->setData($entityMetadata->getLinkField(), $linkFieldId);
        $this->readHandler->execute($product);

        $data = $product->getData();

        $this->assertArrayHasKey('media_gallery', $data);
        $this->assertArrayHasKey('images', $data['media_gallery']);

        $this->assertCount(1, $data['media_gallery']['images']);
        foreach ($data['media_gallery']['images'] as $valueId => $imageData) {
            $this->assertEquals(
                'Image Alt Text',
                $imageData['label']
            );
            $this->assertEquals(
                $imageData['value_id'],
                $valueId
            );
        }
    }
}
