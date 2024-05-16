<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Review;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Registry;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Review\Model\Review;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for adding product reviews mutation
 */
class CreateProductReviewsTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

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
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->reviewCollectionFactory = $objectManager->get(ReviewCollectionFactory::class);
        $this->registry = $objectManager->get(Registry::class);
    }

    /**
     * Test adding a product review as guest and logged in customer
     *
     * @param string $customerName
     * @param bool $isGuest
     *
     * @magentoApiDataFixture Magento/Review/_files/set_position_and_add_store_to_all_ratings.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @dataProvider customerDataProvider
     */
    public function testCustomerAddProductReviews(string $customerName, bool $isGuest)
    {
        $productSku = 'simple_product';
        $query = $this->getQuery($productSku, $customerName);
        $headers = [];

        if (!$isGuest) {
            $headers = $this->getHeaderMap();
        }

        $response = $this->graphQlMutation($query, [], '', $headers);

        $expectedResult = [
            'nickname' => $customerName,
            'summary' => 'Summary Test',
            'text' => 'Text Test',
            'average_rating' => 66.67,
            'ratings_breakdown' => [
                [
                    'name' => 'Price',
                    'value' => 3
                ], [
                    'name' => 'Quality',
                    'value' => 2
                ], [
                    'name' => 'Value',
                    'value' => 5
                ]
            ]
        ];
        self::assertArrayHasKey('createProductReview', $response);
        self::assertArrayHasKey('review', $response['createProductReview']);
        self::assertEquals($expectedResult, $response['createProductReview']['review']);
    }

    /**
     * @magentoConfigFixture default_store catalog/review/allow_guest 0
     */
    public function testAddProductReviewGuestIsNotAllowed()
    {
        $productSku = 'simple_product';
        $customerName = 'John Doe';
        $query = $this->getQuery($productSku, $customerName);
        self::expectExceptionMessage('Guest customers aren\'t allowed to add product reviews.');
        $this->graphQlMutation($query);
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
     * @return array
     */
    public static function customerDataProvider(): array
    {
        return [
            'Guest Customer' => ['John Doe', true],
            'Logged In Customer' => ['John', false],
        ];
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

    /**
     * Get mutation query
     *
     * @param string $sku
     * @param string $customerName
     *
     * @return string
     */
    private function getQuery(string $sku, string $customerName): string
    {
        return <<<QUERY
mutation {
  createProductReview(
    input: {
      sku: "$sku",
      nickname: "$customerName",
      summary: "Summary Test",
      text: "Text Test",
      ratings: [
        {
          id: "Mw==",
          value_id: "MTM="
        }, {
          id: "MQ==",
          value_id: "Mg=="
        }, {
          id: "Mg==",
          value_id: "MTA="
        }
      ]
    }
) {
    review {
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
QUERY;
    }
}
