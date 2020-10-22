<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
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

    /**
     * Test that Packaging popup renders
     *
     * @magentoDataFixture Magento/Shipping/_files/shipping_with_carrier_data.php
     */
    public function testGetCommentsHtml()
    {
        /** @var Template $block */
        $block = $this->objectManager->get(Packaging::class);

        $order = $this->getOrderByIncrementId('100000001');

        /** @var ShipmentTrackInterface $track */
        $shipment = $order->getShipmentsCollection()->getFirstItem();

        $this->registry->register('current_shipment', $shipment);

        $block->setTemplate('Magento_Shipping::order/packaging/popup.phtml');
        $html = $block->toHtml();
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
        $this->assertStringContainsString($expectedNeedle, $html);
    }
}
