<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\Sales\Order\Items;

use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
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

    /**
     * @magentoConfigFixture default_store default/currency/options/base USD
     * @magentoConfigFixture default_store currency/options/default EUR
     * @magentoConfigFixture default_store currency/options/allow USD, EUR
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product with:{"sku":"simple1000","price":10} as:p1
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product with:{"sku":"simple1001","price":20} as:p2
     * @magentoDataFixture Magento\Bundle\Test\Fixture\Option as:opt1
     * @magentoDataFixture Magento\Bundle\Test\Fixture\Product as:bundle1
     * @magentoDataFixtureDataProvider {"opt1":{"product_links":["$p1$","$p2$"]}}
     * @magentoDataFixtureDataProvider {"bundle1":{"sku":"bundle1","_options":["$opt1$"]}}
     * @magentoDataFixture Magento\Bundle\Test\Fixture\OrderItem with:{"items":[{"sku":"$bundle1.sku$"}]} as:order
     *
     * @return void
     */

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
