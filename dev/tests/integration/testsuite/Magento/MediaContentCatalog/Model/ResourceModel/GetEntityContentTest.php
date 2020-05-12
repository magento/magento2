<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Model\ResourceModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\MediaContentApi\Model\GetEntityContentsInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for GetEntityContentsInterface
 */
class GetEntityContentTest extends TestCase
{
    private const CONTENT_TYPE = 'catalog_product';
    private const TYPE = 'entityType';
    private const ENTITY_ID = 'entityId';
    private const FIELD = 'field';

    /**
     * @var GetEntityContentsInterface
     */
    private $getContent;

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $contentIdentityFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getContent = $objectManager->get(GetEntityContentsInterface::class);
        $this->contentIdentityFactory = $objectManager->get(ContentIdentityInterfaceFactory::class);
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
        $contentIdentity = $this->contentIdentityFactory->create(
            [
                self::TYPE => self::CONTENT_TYPE,
                self::FIELD => 'description',
                self::ENTITY_ID => (string) $product->getEntityId(),
            ]
        );
        $this->assertEquals(
            ['Description with <b>html tag</b>'],
            $this->getContent->execute($contentIdentity)
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
        $contentIdentity = $this->contentIdentityFactory->create(
            [
                self::TYPE => self::CONTENT_TYPE,
                self::FIELD => 'description',
                self::ENTITY_ID => (string) $product->getEntityId(),
            ]
        );
        $this->assertEquals(
            [
                '<p>Product base description</p>',
                '<p>Product second description</p>'
            ],
            $this->getContent->execute($contentIdentity)
        );
    }
}
