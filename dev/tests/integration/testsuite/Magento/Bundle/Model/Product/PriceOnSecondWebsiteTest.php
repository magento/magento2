<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\TestFramework\Store\ExecuteInStoreContext;

/**
 * Class to test bundle prices on second website
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceOnSecondWebsiteTest extends PriceAbstract
{
    /**
     * @var ExecuteInStoreContext
     */
    private $executeInStoreContext;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->executeInStoreContext = $this->objectManager->get(ExecuteInStoreContext::class);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/fixed_bundle_product_on_second_website.php
     *
     * @return void
     */
    public function testFixedBundleProductPriceOnSecondWebsite(): void
    {
        $this->executeInStoreContext->execute(
            'fixture_second_store',
            [$this, 'checkBundlePrices'],
            'fixed_bundle_product_with_special_price',
            ['price' => 40, 'final_price' => 12, 'min_price' => 15, 'max_price' => 19.5, 'tier_price' => null],
            ['simple1' => 15, 'simple2' => 15, 'simple3' => 19.5]
        );
        $this->checkBundlePrices(
            'fixed_bundle_product_with_special_price',
            ['price' => 50, 'final_price' => 40, 'min_price' => 48, 'max_price' => 60, 'tier_price' => null],
            ['simple1' => 48, 'simple2' => 50, 'simple3' => 60]
        );
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_on_second_website.php
     *
     * @return void
     */
    public function testDynamicBundleProductPriceOnSecondWebsite(): void
    {
        $this->executeInStoreContext->execute(
            'fixture_second_store',
            [$this, 'checkBundlePrices'],
            'dynamic_bundle_product_with_special_price',
            ['price' => 0, 'final_price' => 0, 'min_price' => 7.5, 'max_price' => 22.5, 'tier_price' => null],
            ['simple1000' => 7.5, 'simple1001' => 22.5]
        );
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_special_price',
            ['price' => 0, 'final_price' => 0, 'min_price' => 7.5, 'max_price' => 15, 'tier_price' => null],
            ['simple1000' => 7.5, 'simple1001' => 15]
        );
    }
}
