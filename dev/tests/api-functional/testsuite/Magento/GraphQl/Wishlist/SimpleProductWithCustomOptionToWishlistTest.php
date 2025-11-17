<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Wishlist;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test to verify add simple product with required field and area options to wishlist via GraphQL
 */
class SimpleProductWithCustomOptionToWishlistTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private CustomerTokenServiceInterface $customerTokenService;

    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private ProductCustomOptionRepositoryInterface $productCustomOptionRepository;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $this->productCustomOptionRepository = $this->objectManager->get(ProductCustomOptionRepositoryInterface::class);
    }

    #[
        Config('wishlist/general/active', '1', 'store', 'default'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple_co',
                'name' => 'Simple CO',
                'price' => 10,
                'options' => [
                    [
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                        'title' => 'field_opt',
                        'is_require' => true,
                    ],
                    [
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                        'title' => 'area_opt',
                        'is_require' => true,
                    ],
                ],
            ],
            as: 'product1'
        ),
        DataFixture(CustomerFixture::class, as: 'customer'),
    ]
    /**
     * Test to verify adding simple product with custom option to wishlist
     *
     * @return void
     */
    public function testAddSimpleProductWithCustomOptionsToWishlist(): void
    {
        $sku = 'simple_co';
        $uids = $this->getEnteredOptionUids($sku, [
            ProductCustomOptionInterface::OPTION_TYPE_FIELD,
            ProductCustomOptionInterface::OPTION_TYPE_AREA,
        ]);
        $query = $this->getMutation($sku, $uids['field'], $uids['area']);
        $response = $this->graphQlMutation($query, [], '', $this->getHeadersMap());
        $this->assertArrayHasKey('addProductsToWishlist', $response);
        $this->assertArrayHasKey('wishlist', $response['addProductsToWishlist']);
        $this->assertEmpty($response['addProductsToWishlist']['user_errors'] ?? []);
        $wishlist = $response['addProductsToWishlist']['wishlist'];
        $this->assertEquals(1, $wishlist['items_count']);
        $this->assertCount(1, $wishlist['items_v2']['items']);
        $item = $wishlist['items_v2']['items'][0];
        $this->assertEquals($sku, $item['product']['sku']);
        $this->assertCount(2, $item['customizable_options']);
        foreach ($item['customizable_options'] as $opt) {
            if ($opt['type'] === 'field') {
                $this->assertEquals('test field value', $opt['values'][0]['value']);
            }
            if ($opt['type'] === 'area') {
                $this->assertEquals('test area value', $opt['values'][0]['value']);
            }
        }
    }

    /**
     * Get addProductsToWishlist mutation
     *
     * @param string $sku
     * @param string $fieldUid
     * @param string $areaUid
     * @param int $wishlistId
     * @return string
     */
    private function getMutation(string $sku, string $fieldUid, string $areaUid, int $wishlistId = 0): string
    {
        return <<<MUTATION
mutation {
  addProductsToWishlist(
    wishlistId: {$wishlistId},
    wishlistItems: [
      {
        sku: "{$sku}"
        quantity: 1
        entered_options: [
          { uid: "{$fieldUid}", value: "test field value" }
          { uid: "{$areaUid}", value: "test area value" }
        ]
      }
    ]
  ) {
    user_errors { code message }
    wishlist {
      id
      items_count
      items_v2(currentPage:1,pageSize:10) {
        items {
          id
          quantity
          customizable_options {
            customizable_option_uid
            label
            type
            values { label value }
          }
          product { sku }
        }
      }
    }
  }
}
MUTATION;
    }

    /**
     * Get product custom option uids
     *
     * @param string $sku
     * @param array $types
     * @return array
     */
    private function getEnteredOptionUids(string $sku, array $types): array
    {
        $customOptions = $this->productCustomOptionRepository->getList($sku);
        $map = [];
        foreach ($customOptions as $customOption) {
            $type = $customOption->getType();
            if (!in_array($type, $types, true)) {
                continue;
            }
            if (!isset($map[$type])) {
                $map[$type] = base64_encode('custom-option/' . (int) $customOption->getOptionId());
            }
            if (count($map) === count($types)) {
                break;
            }
        }
        return [
            'field' => $map[ProductCustomOptionInterface::OPTION_TYPE_FIELD],
            'area' => $map[ProductCustomOptionInterface::OPTION_TYPE_AREA],
        ];
    }

    /**
     * Get customer token for authentication
     *
     * @return string[]
     * @throws AuthenticationException
     * @throws EmailNotConfirmedException
     */
    private function getHeadersMap(): array
    {
        $username = $this->fixtures->get('customer')->getEmail();
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
