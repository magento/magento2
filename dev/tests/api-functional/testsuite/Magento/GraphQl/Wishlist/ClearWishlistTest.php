<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Wishlist;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Wishlist\Test\Fixture\AddProductToWishlist;

/**
 * Test coverage for clearWishlist mutation
 */
class ClearWishlistTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    #[
        Config('wishlist/general/active', true),
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(ProductFixture::class, as: 'product3'),
        DataFixture(ProductFixture::class, as: 'product4'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(AddProductToWishlist::class, [
            'customer_id' => '$customer.id$',
            'product_ids' => [
                '$product1.id$',
                '$product2.id$',
                '$product3.id$',
                '$product4.id$'
            ],
            'name' => 'Test Wish List',
        ], as: 'wishlist')
    ]
    public function testClearWishlist(): void
    {
        $wishlist = $this->fixtures->get('wishlist');
        $wishlistId = (int)$wishlist->getId();

        $this->assertEquals(
            [
                'clearWishlist' => [
                    'user_errors' => [],
                    'wishlist' => [
                        'id' => $wishlistId,
                        'items_count' => 0
                    ]
                ]
            ],
            $this->graphQlMutation(
                $this->getClearWishlistMutation($wishlistId),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    #[
        Config('wishlist/general/active', false),
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(ProductFixture::class, as: 'product3'),
        DataFixture(ProductFixture::class, as: 'product4'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(AddProductToWishlist::class, [
            'customer_id' => '$customer.id$',
            'product_ids' => [
                '$product1.id$',
                '$product2.id$',
                '$product3.id$',
                '$product4.id$'
            ],
            'name' => 'Test Wish List',
        ], as: 'wishlist')
    ]
    public function testClearWishlistWhenConfigDisabled(): void
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('The wishlist configuration is currently disabled.');

        $this->graphQlMutation(
            $this->getClearWishlistMutation((int)$this->fixtures->get('wishlist')->getId()),
            [],
            '',
            $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
        );
    }

    #[
        Config('wishlist/general/active', true),
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(ProductFixture::class, as: 'product3'),
        DataFixture(ProductFixture::class, as: 'product4'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(AddProductToWishlist::class, [
            'customer_id' => '$customer.id$',
            'product_ids' => [
                '$product1.id$',
                '$product2.id$',
                '$product3.id$',
                '$product4.id$'
            ],
            'name' => 'Test Wish List',
        ], as: 'wishlist')
    ]
    public function testClearWishlistAsGuest(): void
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('The current user cannot perform operations on wishlist');
        $this->graphQlMutation($this->getClearWishlistMutation((int)$this->fixtures->get('wishlist')->getId()));
    }

    #[
        Config('wishlist/general/active', true),
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(ProductFixture::class, as: 'product3'),
        DataFixture(ProductFixture::class, as: 'product4'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerFixture::class, as: 'customer2'),
        DataFixture(AddProductToWishlist::class, [
            'customer_id' => '$customer.id$',
            'product_ids' => [
                '$product1.id$',
                '$product2.id$',
                '$product3.id$',
                '$product4.id$'
            ],
            'name' => 'Test Wish List',
        ], as: 'wishlist')
    ]
    public function testClearWishlistForAnotherCustomer(): void
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('The wishlist was not found.');
        $this->graphQlMutation(
            $this->getClearWishlistMutation((int)$this->fixtures->get('wishlist')->getId()),
            [],
            '',
            $this->getCustomerAuthHeaders($this->fixtures->get('customer2')->getEmail())
        );
    }

    /**
     * Get clear wishlist mutation
     *
     * @param int $wishlistId
     * @return string
     */
    private function getClearWishlistMutation(int $wishlistId): string
    {
        return <<<MUTATION
            mutation ClearWishlist {
                clearWishlist(wishlistId: {$wishlistId}) {
                    user_errors {
                        code
                        message
                    }
                    wishlist {
                        id
                        items_count
                    }
                }
            }
        MUTATION;
    }

    /**
     * Returns the header with customer token for GQL Mutation
     *
     * @param string $email
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
