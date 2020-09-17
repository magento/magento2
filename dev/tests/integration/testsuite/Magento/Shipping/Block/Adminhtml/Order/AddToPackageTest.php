<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class verifies packaging popup.
 *
 * @magentoAppArea adminhtml
 */
class AddToPackageTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;
    /**
     * @var OrderInterfaceFactory|mixed
     */
    private $orderFactory;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
    }

    /**
     * Test that Packaging popup renders
     *
     * @magentoDataFixture Magento/GraphQl/Sales/_files/customer_order_with_ups_shipping.php
     */
    public function testGetCommentsHtml()
    {
        /** @var Template $block */
        $block = $this->objectManager->get(Packaging::class);

        $order = $this->orderFactory->create()->loadByIncrementId('100000001');

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
