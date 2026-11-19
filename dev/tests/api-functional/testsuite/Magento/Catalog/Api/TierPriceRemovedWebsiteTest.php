<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tier prices of a website the product is removed from are kept, like its other website-level values.
 *
 * @see https://github.com/magento/magento2/issues/38099
 */
class TierPriceRemovedWebsiteTest extends WebapiAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @param string $unassign how the product is removed from the second website
     * @return void
     */
    #[
        Config('catalog/price/scope', '1'),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$group2.id$'], 'store2'),
        DataFixture(ProductFixture::class, ['website_ids' => [1, '$website2.id$']], 'product'),
        \PHPUnit\Framework\Attributes\TestWith(['product save']),
        \PHPUnit\Framework\Attributes\TestWith(['website link delete']),
    ]
    public function testTierPricesOfRemovedWebsite(string $unassign): void
    {
        $sku = $this->fixtures->get('product')->getSku();
        $websiteId = (int)$this->fixtures->get('website2')->getId();
        $this->call('/V1/products/tier-prices', Request::HTTP_METHOD_POST, [
            'prices' => [
                $this->tierPrice($sku, 0, 2, 30),
                $this->tierPrice($sku, $websiteId, 1, 33),
            ],
        ]);
        $this->assertSame([[0, 2.0, 30.0], [$websiteId, 1.0, 33.0]], $this->getTierPrices($sku), 'Precondition.');

        if ($unassign === 'product save') {
            $this->call('/V1/products/' . $sku, Request::HTTP_METHOD_PUT, [
                'product' => ['sku' => $sku, 'extension_attributes' => ['website_ids' => [1]]],
            ]);
        } else {
            $this->call('/V1/products/' . $sku . '/websites/' . $websiteId, Request::HTTP_METHOD_DELETE, []);
        }

        $this->assertSame(
            [[0, 2.0, 30.0], [$websiteId, 1.0, 33.0]],
            $this->getTierPrices($sku),
            'Removing the website keeps its tier price, like its other website values.'
        );
    }

    /**
     * Tier price request data
     *
     * @param string $sku
     * @param int $websiteId
     * @param float $qty
     * @param float $price
     * @return array
     */
    private function tierPrice(string $sku, int $websiteId, float $qty, float $price): array
    {
        return [
            'sku' => $sku,
            'price' => $price,
            'price_type' => 'fixed',
            'website_id' => $websiteId,
            'customer_group' => 'NOT LOGGED IN',
            'quantity' => $qty,
        ];
    }

    /**
     * Website id, quantity and price of each tier price of the product, ordered by website
     *
     * @param string $sku
     * @return array
     */
    private function getTierPrices(string $sku): array
    {
        $rows = [];
        $prices = $this->call('/V1/products/tier-prices-information', Request::HTTP_METHOD_POST, ['skus' => [$sku]]);
        foreach ($prices as $price) {
            $rows[] = [(int)$price['website_id'], (float)$price['quantity'], (float)$price['price']];
        }
        sort($rows);

        return $rows;
    }

    /**
     * Call a REST route
     *
     * @param string $path
     * @param string $method
     * @param array $data
     * @return mixed
     */
    private function call(string $path, string $method, array $data)
    {
        return $this->_webApiCall(['rest' => ['resourcePath' => $path, 'httpMethod' => $method]], $data);
    }
}
