<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * A bundle whose required options each have one selection can be added from product lists without bundle_option
 */
class SingleChoiceAddToCartTest extends TestCase
{
    #[
        DataFixture(ProductFixture::class, as: 's1'),
        DataFixture(ProductFixture::class, as: 's2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$s1.sku$', 'is_default' => true], 'link1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$s2.sku$', 'is_default' => true], 'link2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['type' => 'checkbox', 'product_links' => ['$link2$']], 'opt2'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$', '$opt2$']], 'bundle'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testAddWithoutBundleOptionStoresDefaultSelectionsInBuyRequest(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $product = $objectManager->get(ProductRepositoryInterface::class)
            ->get($fixtures->get('bundle')->getSku(), false, null, true);
        /** @var Quote $quote */
        $quote = $objectManager->get(CartRepositoryInterface::class)->get($fixtures->get('cart')->getId());

        $item = $quote->addProduct($product, new DataObject(['qty' => 1]));
        $this->assertIsNotString($item, is_string($item) ? $item : '');
        $item->checkData();

        $this->assertFalse((bool)$item->getHasError(), (string)$item->getMessage());
        $this->assertEquals($this->getSelectionsByOption($product), $item->getBuyRequest()->getBundleOption());
    }

    private function getSelectionsByOption(ProductInterface $bundle): array
    {
        $typeInstance = $bundle->getTypeInstance();
        $selections = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($bundle), $bundle);
        $this->assertCount(2, $selections);
        $result = [];
        foreach ($selections as $selection) {
            $result[(int)$selection->getOptionId()] = (int)$selection->getSelectionId();
        }

        return $result;
    }
}
