<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\View;

use Magento\Config\Model\Config\Factory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\Order\Address\Renderer as OrderAddressRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\View\AddressInfo
 */
class AddressInfoTest extends TestCase
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
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->orderAddressRenderer = $this->objectManager->get(OrderAddressRenderer::class);
    }

    /**
     * Verify customer address attributes (e.g. Company) are visible on second website order.
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     * @magentoDataFixture Magento/Sales/_files/order_on_second_website.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testCompanyAddressAttributeVisibleForOrderOnSecondWebsite()
    {
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $website = $storeManager->getWebsites(false, true)['base'];
        $configData = [
            'section' => 'customer',
            'website' => $website->getId(),
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
        $orderFixtureStore = $this->objectManager->create(Order::class)->loadByIncrementId('100000001');
        $address = $orderFixtureStore->getBillingAddress();
        self::assertStringContainsString('Test Company', $this->orderAddressRenderer->format($address, 'html'));
    }
}
