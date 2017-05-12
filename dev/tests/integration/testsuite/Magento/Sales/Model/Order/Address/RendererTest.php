<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Address;

use Magento\Config\Model\ResourceModel\Config as ConfigResourceModel;
use Magento\Framework\App\ReinitableConfig;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Address\Renderer as OrderAddressRenderer;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class RendererTest test address templates render in store scope.
 */
class RendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object manager instance.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Order address renderer instance.
     *
     * @var OrderAddressRenderer
     */
    private $orderAddressRenderer;

    /**
     * Config resource model instance.
     *
     * @var ConfigResourceModel
     */
    private $configResourceModel;

    /**
     * Reinitable config instance.
     *
     * @var ReinitableConfig
     */
    private $reinitableConfig;

    /**
     * Prepare objects for test.
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->orderAddressRenderer = $this->objectManager->get(OrderAddressRenderer::class);
        $this->configResourceModel = $this->objectManager->get(ConfigResourceModel::class);
        $this->reinitableConfig = $this->objectManager->get(ReinitableConfig::class);
    }

    /**
     * Format address test.
     *
     * @magentoDataFixture Magento/Sales/_files/order_fixture_store.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testFormat()
    {
        $addressTemplates = [
            'text' => 'text_customized',
            'oneline' => 'oneline_customized',
            'html' => 'html_customized',
            'pdf' => 'pdf_customized'
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
        $this->reinitableConfig->reinit();

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
}
