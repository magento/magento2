<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class verifies packaging popup.
 *
 * @magentoAppArea adminhtml
 */
class AddToPackageTest extends TestCase
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
    }

    /**
     * Test that Packaging popup renders
     *
     * @magentoDataFixture Magento/Shipping/_files/shipping_with_carrier_data.php
     */
    public function testGetCommentsHtml(): void
    {
        $expectedNeedle = "packaging.setItemQtyCallback(function(itemId){
            var item = $$('[name=\"shipment[items]['+itemId+']\"]')[0],
                itemTitle = $('order_item_' + itemId + '_title');
            if (!itemTitle && !item) {
                return 0;
            }
            if (item && !isNaN(item.value)) {
                return item.value;
            }
        });";
        $this->assertStringContainsString($expectedNeedle, $this->getHtml());
    }

    /**
     * Verify currency code on custom value field
     *
     * @magentoDataFixture Magento/Shipping/_files/shipping_with_carrier_data_different_currency_code.php
     */
    public function testGetCurrencyCodeCustomValue()
    {
        $template = '/<span class="customs-value-currency">\s*?(?<currency>[A-Za-z]+)\s*?<\/span>/';
        $matches = [];
        preg_match($template, $this->getHtml(), $matches);
        $currency = $matches['currency'] ?? null;
        $this->assertEquals('FR', $currency);
    }

    /**
     * Get html for packaging popup
     *
     * @return string
     */
    private function getHtml()
    {
        /** @var Template $block */
        $block = $this->objectManager->get(Packaging::class);

        $order = $this->getOrderByIncrementId('100000001');

        /** @var ShipmentTrackInterface $track */
        $shipment = $order->getShipmentsCollection()->getFirstItem();

        $this->registry->register('current_shipment', $shipment);

        $block->setTemplate('Magento_Shipping::order/packaging/popup.phtml');
        $block->setNameInLayout('test');

        return $block->toHtml();
    }

    /**
     * Loads order entity by provided order increment ID.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrderByIncrementId(string $incrementId) : OrderInterface
    {
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->objectManager->get(SearchCriteriaBuilder::class)
            ->addFilter('increment_id', $incrementId)
            ->create();

        $items = $this->orderRepository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }
}
