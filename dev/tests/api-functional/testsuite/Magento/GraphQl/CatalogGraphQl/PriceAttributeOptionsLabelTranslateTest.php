<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogGraphQl;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Eav\Model\Entity\Attribute\FrontendLabel;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test class to verify the translated price attribute option label based on the store view.
 */
class PriceAttributeOptionsLabelTranslateTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixture;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixture = DataFixtureStorageManager::getStorage();
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    #[
        DataFixture(
            StoreFixture::class,
            [
                'code' => 'view2',
                'name' => 'view2'
            ],
            as: 'view2'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple'
            ],
            as: 'product'
        ),
    ]

    public function testValidatePriceAttributeOptionsLabelTranslationForSecondStoreView(): void
    {
        $attributeCode = 'price';
        $secondStoreViewFixtureName = 'view2';
        $attributeStoreFrontLabelForSecondStoreView = 'Price View2';

        //Updating price attribute storefront option label for the second store view.
        $attributeRepository = $this->objectManager->create(ProductAttributeRepositoryInterface::class);

        $priceAttribute = $attributeRepository->get($attributeCode);

        $frontendLabelAttribute = $this->objectManager->get(FrontendLabel::class);
        $frontendLabelAttribute->setStoreId(
            $this->fixture->get($secondStoreViewFixtureName)->getId()
        );
        $frontendLabelAttribute->setLabel($attributeStoreFrontLabelForSecondStoreView);

        $frontendLabels = $priceAttribute->getFrontendLabels();
        $frontendLabels[] = $frontendLabelAttribute;

        $priceAttribute->setFrontendLabels($frontendLabels);
        $attributeRepository->save($priceAttribute);

        $query = $this->getProductsQueryWithAggregations();
        $headers = ['Store' => $secondStoreViewFixtureName];
        $response = $this->graphQlQuery($query, [], '', $headers);
        $this->assertNotEmpty($response['products']['aggregations']);
        $aggregationAttributes = $response['products']['aggregations'];
        $priceAttributeOptionLabel = '';

        foreach ($aggregationAttributes as $attribute) {
            if ($attribute['attribute_code'] === $attributeCode) {
                $priceAttributeOptionLabel = $attribute['label'];
            }
        }

        $this->assertEquals($priceAttributeOptionLabel, 'Price View2');
    }

    /**
     * Prepare products query with aggregations
     *
     * @return string
     */
    private function getProductsQueryWithAggregations() : string
    {
        return <<<QUERY
{
    products(
        currentPage: 1,
        pageSize:12,
        filter: {
        sku: {
            eq: "simple"
        }
    }, sort: {
        price: ASC
    }) {

        aggregations {
            options {
                count,
                label,
                value
            }, attribute_code, count, label
        }
    }
}
QUERY;
    }
}
