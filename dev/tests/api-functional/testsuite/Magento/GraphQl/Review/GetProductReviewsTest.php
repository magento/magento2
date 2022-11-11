<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Review;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Registry;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\Review\SummaryFactory;
use Magento\Review\Test\Fixture\Review as ReviewFixture;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for product reviews queries
 */
class GetProductReviewsTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var ReviewCollectionFactory
     */
    private $reviewCollectionFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->reviewCollectionFactory = $objectManager->get(ReviewCollectionFactory::class);
        $this->registry = $objectManager->get(Registry::class);
    }

    /**
     * @magentoApiDataFixture Magento/Review/_files/set_position_and_add_store_to_all_ratings.php
     */
    public function testProductReviewRatingsMetadata()
    {
        $query
            = <<<QUERY
{
  productReviewRatingsMetadata {
    items {
      id
      name
      values {
        value_id
        value
      }
    }
  }
}
QUERY;
        $expectedRatingItems = [
            [
                'id' => 'Mw==',
                'name' => 'Price',
                'values' => [
                    [
                        'value_id' => 'MTE=',
                        'value' => "1"
                    ],[
                        'value_id' => 'MTI=',
                        'value' => "2"
                    ],[
                        'value_id' => 'MTM=',
                        'value' => "3"
                    ],[
                        'value_id' => 'MTQ=',
                        'value' => "4"
                    ],[
                        'value_id' => 'MTU=',
                        'value' => "5"
                    ]
                ]
            ], [
                'id' => 'MQ==',
                'name' => 'Quality',
                'values' => [
                    [
                        'value_id' => 'MQ==',
                        'value' => "1"
                    ],[
                        'value_id' => 'Mg==',
                        'value' => "2"
                    ],[
                        'value_id' => 'Mw==',
                        'value' => "3"
                    ],[
                        'value_id' => 'NA==',
                        'value' => "4"
                    ],[
                        'value_id' => 'NQ==',
                        'value' => "5"
                    ]
                ]
            ], [
                'id' => 'Mg==',
                'name' => 'Value',
                'values' => [
                    [
                        'value_id' => 'Ng==',
                        'value' => "1"
                    ],[
                        'value_id' => 'Nw==',
                        'value' => "2"
                    ],[
                        'value_id' => 'OA==',
                        'value' => "3"
                    ],[
                        'value_id' => 'OQ==',
                        'value' => "4"
                    ],[
                        'value_id' => 'MTA=',
                        'value' => "5"
                    ]
                ]
            ]
        ];
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('productReviewRatingsMetadata', $response);
        self::assertArrayHasKey('items', $response['productReviewRatingsMetadata']);
        self::assertNotEmpty($response['productReviewRatingsMetadata']['items']);
        self::assertEquals($expectedRatingItems, $response['productReviewRatingsMetadata']['items']);
    }

    /**
     * @magentoApiDataFixture Magento/Review/_files/different_reviews.php
     */
    public function testProductReviewRatings()
    {
        $productSku = 'simple';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        // phpcs:ignore
        $summaryFactory = ObjectManager::getInstance()->get(SummaryFactory::class);
        $storeId = ObjectManager::getInstance()->get(StoreManagerInterface::class)->getStore()->getId();
        $summary = $summaryFactory->create()->setStoreId($storeId)->load($product->getId());
        $query
            = <<<QUERY
{
  products(filter: {
      sku: {
          eq: "$productSku"
      }
  }) {
    items {
      rating_summary
      review_count
      reviews {
        items {
          nickname
          summary
          text
          average_rating
          product {
            sku
            name
          }
          ratings_breakdown {
            name
            value
          }
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('products', $response);
        self::assertArrayHasKey('items', $response['products']);
        self::assertNotEmpty($response['products']['items']);

        $items = $response['products']['items'];
        self::assertEquals($summary->getData('rating_summary'), $items[0]['rating_summary']);
        self::assertEquals($summary->getData('reviews_count'), $items[0]['review_count']);
        self::assertArrayHasKey('items', $items[0]['reviews']);
        self::assertNotEmpty($items[0]['reviews']['items']);
    }

    /**
     * @magentoApiDataFixture Magento/Review/_files/customer_review_with_rating.php
     */
    public function testCustomerReviewsAddedToProduct()
    {
        $query = <<<QUERY
{
  customer {
    reviews {
      items {
        nickname
        summary
        text
        average_rating
        ratings_breakdown {
          name
          value
        }
      }
    }
  }
}
QUERY;
        $expectedFirstItem = [
            'nickname' => 'Nickname',
            'summary' => 'Review Summary',
            'text' => 'Review text',
            'average_rating' => 40,
            'ratings_breakdown' => [
                [
                    'name' => 'Quality',
                    'value' => 2
                ],[
                    'name' => 'Value',
                    'value' => 2
                ]
            ]
        ];
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('customer', $response);
        self::assertArrayHasKey('reviews', $response['customer']);
        self::assertArrayHasKey('items', $response['customer']['reviews']);
        self::assertNotEmpty($response['customer']['reviews']['items']);
        self::assertEquals($expectedFirstItem, $response['customer']['reviews']['items'][0]);
    }

    #[
        DataFixture(StoreFixture::class, ['code' => 'store2'], 'store2'),
        DataFixture(ProductFixture::class, ['sku' => 'product1'], 'product1'),
        DataFixture(ReviewFixture::class, ['entity_pk_value' => '$product1.id$']),
        DataFixture(ReviewFixture::class, ['entity_pk_value' => '$product1.id$', 'store_id' => '$store2.id$']),
    ]
    /**
     * @dataProvider storesDataProvider
     * @param string $storeCode
     */
    public function testProductReviewDifferentStores(string $storeCode): void
    {
        $productSku = 'product1';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "$productSku"}}) {
    items {
      review_count
      reviews {
        items {
          nickname
          summary
          text
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query, [], '', ['Store' => $storeCode]);
        self::assertArrayHasKey('products', $response);
        self::assertArrayHasKey('items', $response['products']);
        self::assertNotEmpty($response['products']['items']);
        self::assertEquals(1, $response['products']['items'][0]['review_count']);
        self::assertCount(1, $response['products']['items'][0]['reviews']['items']);
    }

    /**
     * @return array
     */
    public function storesDataProvider(): array
    {
        return [
            ['default'],
            ['store2'],
        ];
    }

    /**
     * Removing the recently added product reviews
     */
    public function tearDown(): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        $productId = 1;
        /** @var Collection $reviewsCollection */
        $reviewsCollection = $this->reviewCollectionFactory->create();
        $reviewsCollection->addEntityFilter(Review::ENTITY_PRODUCT_CODE, $productId);
        /** @var Review $review */
        foreach ($reviewsCollection as $review) {
            $review->delete();
        }
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);

        parent::tearDown();
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return array
     *
     * @throws AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);

        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
