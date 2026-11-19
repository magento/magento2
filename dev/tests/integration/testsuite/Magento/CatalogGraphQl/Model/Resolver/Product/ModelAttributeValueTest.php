<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Area;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use PHPUnit\Framework\TestCase;

/**
 * A user-defined product attribute with code "model" exposed as a dynamic product field
 */
#[
    AppArea(Area::AREA_GRAPHQL),
    AppIsolation(true),
]
class ModelAttributeValueTest extends TestCase
{
    #[
        DataFixture(
            AttributeFixture::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'attribute_code' => 'model',
                'is_visible_on_front' => 1,
            ],
            'model_attribute'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'custom_attributes' => [
                    ['attribute_code' => 'model', 'value' => 'MX-500'],
                ],
            ],
            'product'
        ),
    ]
    public function testProductsQueryReturnsModelAttributeValue(): void
    {
        // The GraphQL schema is built from attributes and may already be held in memory without this one
        CacheCleaner::cleanAll();
        Bootstrap::getInstance()->reinitialize();
        Bootstrap::getInstance()->loadArea(Area::AREA_GRAPHQL);
        $objectManager = Bootstrap::getObjectManager();

        $sku = DataFixtureStorageManager::getStorage()->get('product')->getSku();
        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$sku}"}}) {
        items {
            sku
            model
        }
    }
}
QUERY;

        $response = $objectManager->get(GraphQlRequest::class)->send($query);
        $result = $objectManager->get(SerializerInterface::class)->unserialize((string)$response->getBody());

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertSame(
            [['sku' => $sku, 'model' => 'MX-500']],
            $result['data']['products']['items']
        );
    }
}
