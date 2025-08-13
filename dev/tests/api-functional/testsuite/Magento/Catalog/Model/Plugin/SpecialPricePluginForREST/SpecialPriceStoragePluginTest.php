<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Plugin\SpecialPricePluginForREST;

use Magento\Authorization\Test\Fixture\Role as RoleFixture;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Test\Fixture\User as UserFixture;

/**
 * WebAPI test to validate SpecialPriceStoragePlugin behavior
 */
class SpecialPriceStoragePluginTest extends WebapiAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(RoleFixture::class, as: 'restrictedRole'),
        DataFixture(UserFixture::class, ['role_id' => '$restrictedRole.id$'], 'restrictedUser'),
    ]
    public function testSpecialPriceIsAppliedToAllStoresInWebsite(): void
    {
        $this->_markTestAsRestOnly();
        $product = $this->fixtures->get('product');
        $sku = $product->getSku();
        $storeId= $product->getStoreId();
        $product->setSpecialPrice(123.45);

        $Store = $this->storeManager->getStore($storeId);
        $website = $Store->getWebsite();
        $storeIds = $website->getStoreIds();

        $restrictedUser = $this->fixtures->get('restrictedUser');

        $adminTokens = Bootstrap::getObjectManager()->get(AdminTokenServiceInterface::class);
        $accessToken = $adminTokens->createAdminAccessToken(
            $restrictedUser->getData('username'),
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        $data = [
            'sku' => $sku,
            'price' =>123.45,
            'store_id' => $storeId
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/special-price',
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $accessToken,
            ],
        ];
        $this->_webApiCall(
            $serviceInfo,
            [
                'prices' => [
                    $data
                ]
            ]
        );
        foreach ($storeIds as $storeId) {
            $product = $this->productRepository->get($sku, false, $storeId);
            $this->assertNotNull(
                $product->getSpecialPrice(),
                "Expected special price for SKU '$sku' in store ID $storeId, but got none."
            );
            $this->assertEquals(
                123.45,
                (float)$product->getSpecialPrice(),
                "Special price mismatch for store ID $storeId"
            );
        }
    }
}
