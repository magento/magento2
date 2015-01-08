<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config;

/**
 * Class CreditmemoCreateTest
 */
class CreditmemoCreateTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/creditmemo';

    const SERVICE_READ_NAME = 'salesCreditmemoRepositoryV1';

    const SERVICE_VERSION = 'V1';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/invoice.php
     */
    public function testInvoke()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $orderCollection = $this->objectManager->get('Magento\Sales\Model\Resource\Order\Collection');
        $order = $orderCollection->getFirstItem();

//        $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId('100000001');
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = current($order->getAllItems());
        $items = [
            $orderItem->getId() => ['qty' => $orderItem->getQtyInvoiced(), 'order_item_id' => $orderItem->getId()],
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Config::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'save',
            ],
        ];
        $data = [
            'adjustment' => null,
            'adjustment_negative' => null,
            'adjustment_positive' => null,
            'base_adjustment' => null,
            'base_adjustment_negative' => null,
            'base_adjustment_positive' => null,
            'base_currency_code' => null,
            'base_discount_amount' => null,
            'base_grand_total' => null,
            'base_hidden_tax_amount' => null,
            'base_shipping_amount' => null,
            'base_shipping_hidden_tax_amnt' => null,
            'base_shipping_incl_tax' => null,
            'base_shipping_tax_amount' => null,
            'base_subtotal' => null,
            'base_subtotal_incl_tax' => null,
            'base_tax_amount' => null,
            'base_to_global_rate' => null,
            'base_to_order_rate' => null,
            'billing_address_id' => null,
            'created_at' => null,
            'creditmemo_status' => null,
            'discount_amount' => null,
            'discount_description' => null,
            'email_sent' => null,
            'entity_id' => null,
            'global_currency_code' => null,
            'grand_total' => null,
            'hidden_tax_amount' => null,
            'increment_id' => null,
            'invoice_id' => null,
            'order_currency_code' => null,
            'order_id' => $order->getId(),
            'shipping_address_id' => null,
            'shipping_amount' => null,
            'shipping_hidden_tax_amount' => null,
            'shipping_incl_tax' => null,
            'shipping_tax_amount' => null,
            'state' => null,
            'store_currency_code' => null,
            'store_id' => null,
            'store_to_base_rate' => null,
            'store_to_order_rate' => null,
            'subtotal' => null,
            'subtotal_incl_tax' => null,
            'tax_amount' => null,
            'transaction_id' => null,
            'updated_at' => null,
            'items' => $items,
        ];
        $result = $this->_webApiCall($serviceInfo, ['entity' => $data]);
        $this->assertNotEmpty($result);
    }
}
