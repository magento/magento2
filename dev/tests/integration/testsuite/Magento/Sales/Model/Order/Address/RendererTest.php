<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Address;

use Magento\Config\Model\Config\Factory;
use Magento\Config\Model\ResourceModel\Config as ConfigResourceModel;
use Magento\Framework\App\Config;
use Magento\Framework\Locale\TranslatedLists;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Address\Renderer as OrderAddressRenderer;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Sales\Model\Order\Address\Renderer.
 */
class RendererTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var OrderAddressRenderer
     */
    private $orderAddressRenderer;

    /**
     * @var ConfigResourceModel
     */
    private $configResourceModel;

    /**
     * @var Config
     */
    private $config;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->orderAddressRenderer = $this->objectManager->get(OrderAddressRenderer::class);
        $this->configResourceModel = $this->objectManager->get(ConfigResourceModel::class);
        $this->config = $this->objectManager->get(Config::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_fixture_store.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testFormat()
    {
        $addressTemplates = [
            'text' => 'text_customized',
            'oneline' => 'oneline_customized',
            'html' => 'html_customized',
            'pdf' => 'pdf_customized',
        ];

        /** @var Store $store */
        $store = $this->objectManager->create(Store::class);
        $storeId = $store->load('fixturestore')->getStoreId();

        $this->configResourceModel->saveConfig(
            'customer/address_templates/text',
            $addressTemplates['text'],
            'stores',
            $storeId
        );
        $this->configResourceModel->saveConfig(
            'customer/address_templates/oneline',
            $addressTemplates['oneline'],
            'stores',
            $storeId
        );
        $this->configResourceModel->saveConfig(
            'customer/address_templates/html',
            $addressTemplates['html'],
            'stores',
            $storeId
        );
        $this->configResourceModel->saveConfig(
            'customer/address_templates/pdf',
            $addressTemplates['pdf'],
            'stores',
            $storeId
        );
        $this->config->clean();

        /** @var Order $order */
        $order = $this->objectManager->create(Order::class)
            ->loadByIncrementId('100000004');

        /** @var OrderAddress $address */
        $address = $order->getBillingAddress();

        $this->assertEquals($addressTemplates['text'], $this->orderAddressRenderer->format($address, 'text'));
        $this->assertEquals($addressTemplates['oneline'], $this->orderAddressRenderer->format($address, 'oneline'));
        $this->assertEquals($addressTemplates['html'], $this->orderAddressRenderer->format($address, 'html'));
        $this->assertEquals($addressTemplates['pdf'], $this->orderAddressRenderer->format($address, 'pdf'));
    }

    /**
     * Verify customer address attributes (e.g. Company) are respecting website scope configuration.
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     * @magentoDataFixture Magento/Sales/_files/order_on_second_website.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testFormatNonDisplayedCompanyField()
    {
        $orderFixtureStore = $this->objectManager->create(Order::class)->loadByIncrementId('100000001');
        $configData = [
            'section' => 'customer',
            'website' => $orderFixtureStore->getStore()->getWebsite()->getId(),
            'store' => null,
            'groups' => [
                'address' => [
                    'fields' => [
                        'company_show' => ['value' => ''],
                    ],
                ],
            ],
        ];
        $configFactory = $this->objectManager->get(Factory::class);
        $config = $configFactory->create(['data' => $configData]);
        $config->save();
        $address = $orderFixtureStore->getBillingAddress();
        self::assertStringNotContainsString('Test Company', $this->orderAddressRenderer->format($address, 'html'));
    }

    /**
     * Order country will be translated to locale on which was placed an order
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Sales/_files/order_fixture_store.php
     *
     * @return void
     */
    public function testRenderOrderAddressCountry(): void
    {
        /** @var TranslatedLists $localeResolver */
        $this->objectManager->create(TranslatedLists::class, ['locale' => 'ko_KR']);

        /** @var Order $order */
        $order = $this->objectManager->create(Order::class)
            ->loadByIncrementId('100000004');

        /** @var OrderAddress $address */
        $address = $order->getBillingAddress();

        $this->assertStringContainsString(
            'United States',
            $this->orderAddressRenderer->format($address, 'html')
        );
    }
}
