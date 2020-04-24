<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
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
     * @inheritdoc
     */
    public function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getContent = $objectManager->get(GetContent::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Test for get content from product in different store views
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testProduct(): void
    {
        $product = $this->productRepository->get('simple');
        $this->assertEquals(
            'Description with <b>html tag</b>',
            $this->getContent->execute((int) $product->getEntityId(), $product->getAttributes()['description'])
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
        $this->assertEquals(
            '<p>Product base description</p>' . PHP_EOL . '<p>Product second description</p>',
            $this->getContent->execute((int) $product->getEntityId(), $product->getAttributes()['description'])
        );
    }
}
