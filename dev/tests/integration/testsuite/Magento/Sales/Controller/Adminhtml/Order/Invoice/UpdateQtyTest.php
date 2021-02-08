<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Xpath;

/**
 * Class tests invoice items qty update.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class UpdateQtyTest extends AbstractInvoiceControllerTest
{
    /** @var SerializerInterface */
    private $json;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->json = $this->_objectManager->get(SerializerInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testSuccess(): void
    {
        $order = $this->getOrder('100000001');
        $itemId = $order->getItemsCollection()->getFirstItem()->getId();
        $qtyToInvoice = 1;
        $invoicedItemsXpath = sprintf(
            "//input[contains(@class, 'qty-input') and @name='invoice[items][%u]' and @value='%u']",
            $itemId,
            $qtyToInvoice
        );
        $post = $this->hydratePost([$itemId => $qtyToInvoice]);
        $this->prepareRequest($post, ['order_id' => $order->getEntityId()]);
        $this->dispatch('backend/sales/order_invoice/updateQty');
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($invoicedItemsXpath, $this->getResponse()->getContent())
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_bundle_and_invoiced.php
     *
     * @return void
     */
    public function testCanNotInvoice(): void
    {
        $order = $this->getOrder('100000001');
        $itemId = $order->getItemsCollection()->getFirstItem()->getId();
        $post = $this->hydratePost([$itemId => '1']);
        $this->prepareRequest($post, ['order_id' => $order->getEntityId()]);
        $this->dispatch('backend/sales/order_invoice/updateQty');
        $this->assertErrorResponse('The order does not allow an invoice to be created.');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testWithoutQty(): void
    {
        $order = $this->getOrder('100000001');
        $itemId = $order->getItemsCollection()->getFirstItem()->getId();
        $post = $this->hydratePost([$itemId => '0']);
        $this->prepareRequest($post, ['order_id' => $order->getEntityId()]);
        $this->dispatch('backend/sales/order_invoice/updateQty');
        $this->assertErrorResponse(
            'The invoice can\'t be created without products. Add products and try again.'
        );
    }

    /**
     * @return void
     */
    public function testWithNoExistingOrderId(): void
    {
        $post = $this->hydratePost([
            'invoice' => [
                'items' => [
                    '1' => '3',
                ],
            ],
        ]);
        $this->prepareRequest($post, ['order_id' => 6543265]);
        $this->dispatch('backend/sales/order_invoice/updateQty');
        $this->assertErrorResponse('The order no longer exists.');
    }

    /**
     * Check error response
     *
     * @param string $expectedMessage
     * @return void
     */
    private function assertErrorResponse(string $expectedMessage): void
    {
        $expectedResponse = [
            'error' => true,
            'message' => (string)__($expectedMessage),
        ];
        $response = $this->getResponse()->getContent();
        $this->assertNotEmpty($response);
        $this->assertEquals($expectedResponse, $this->json->unserialize($response));
    }
}
