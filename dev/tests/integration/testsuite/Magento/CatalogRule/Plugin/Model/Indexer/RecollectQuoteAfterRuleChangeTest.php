<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Plugin\Model\Indexer;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Test\Fixture\Rule as CatalogRuleFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class RecollectQuoteAfterRuleChangeTest extends TestCase
{
    #[
        DbIsolation(false),
        DataFixture(ProductFixture::class, ['type_id' => 'simple', 'price' => 100], as: 'product'),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'simple_action' => 'by_percent',
                'discount_amount' => 50,
                'conditions' => [],
                'actions' => [],
                'website_ids' => [1],
                'customer_group_ids' => [0, 1],
                'is_active' => 1
            ],
            as: 'catalog_rule'
        ),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        )
    ]
    public function testRuleChangeInUpdateOnSaveModeMarksQuoteForRecollect(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $ruleRepository = $objectManager->get(CatalogRuleRepositoryInterface::class);
        $connection = $objectManager->get(ResourceConnection::class)->getConnection();
        $cartId = (int)$fixtures->get('cart')->getId();
        $connection->update('quote', ['trigger_recollect' => 0], ['entity_id = ?' => $cartId]);

        $rule = $ruleRepository->get((int)$fixtures->get('catalog_rule')->getId());
        $rule->setDiscountAmount(40);
        $ruleRepository->save($rule);

        $this->assertEquals(
            1,
            $connection->fetchOne('SELECT trigger_recollect FROM quote WHERE entity_id = ?', [$cartId])
        );
    }
}
