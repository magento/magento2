<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\Sales\Order\Items;

use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\OrderItem as OrderItemFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;
    /**
     * @var Session
     */
    protected $session;

    /** @var Renderer */
    private $block;

    /**
     * @defaultDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $layout->createBlock(Renderer::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    #[
        DbIsolation(false),
        Config('default/currency/options/base', 'USD', 'store', 'default'),
        Config('currency/options/default', 'EUR', 'store', 'default'),
        Config('currency/options/allow', 'USD, EUR', 'store', 'default'),
        DataFixture(ProductFixture::class, ['price' => 10], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'p2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$', '$p2$']], 'opt1'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$']], 'bundle1'),
        DataFixture(OrderItemFixture::class, ['items' => [['sku' => '$bundle1.sku$']]], 'order'),
    ]
    public function testOrderEmailContent(): void
    {
        $order = $this->objectManager->create(Order::class);

        $incrementId =  $this->fixtures->get('order')->getIncrementId();
        $order->loadByIncrementId($incrementId);

        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $currencyCode = $storeManager->getWebsite()->getDefaultStore()->getDefaultCurrency()->getCode();
        $storeId = $this->objectManager->get(StoreManagerInterface::class)->getStore()->getId();
        $order->setStoreId($storeId);
        $order->setOrderCurrencyCode($currencyCode);
        $order->save();

        $priceBlockHtml = [];

        $items = $order->getAllItems();
        foreach ($items as $item) {
            $item->setProductOptions([
                'bundle_options' => [
                    [
                        'value' => [
                            ['title' => '']
                        ],
                    ],
                ],
                'bundle_selection_attributes' => '{"qty":5 ,"price":99}'
            ]);
            $this->block->setItem($item);
            $priceBlockHtml[] = $this->block->getValueHtml($item);
        }

        $this->assertStringContainsString("€99", $priceBlockHtml[0]);
    }
}
