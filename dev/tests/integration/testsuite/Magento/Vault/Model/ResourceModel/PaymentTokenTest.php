<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\ResourceModel;

use Magento\Braintree\Model\Ui\PayPal\ConfigProvider as PayPalConfigProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Setup\InstallSchema;

class PaymentTokenTest extends \PHPUnit_Framework_TestCase
{
    const CUSTOMER_ID = 1;
    const TOKEN = 'mx29vk';
    const ORDER_INCREMENT_ID = '100000001';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PaymentToken
     */
    private $paymentToken;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * @var Order
     */
    private $order;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->order = $this->objectManager->create(Order::class);
        $this->paymentToken = $this->objectManager->create(PaymentToken::class);
        $this->paymentTokenManagement = $this->objectManager->get(PaymentTokenManagement::class);

        $this->resource = $this->objectManager->get(ResourceConnection::class);
        $this->connection = $this->resource->getConnection();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Braintree/_files/paypal_vault_token.php
     */
    public function testAddLinkToOrderPaymentExists()
    {
        $this->order->loadByIncrementId(self::ORDER_INCREMENT_ID);
        $paymentToken = $this->paymentTokenManagement
            ->getByGatewayToken(self::TOKEN, PayPalConfigProvider::PAYPAL_CODE, self::CUSTOMER_ID);

        $this->connection->insert(
            $this->resource->getTableName(InstallSchema::ORDER_PAYMENT_TO_PAYMENT_TOKEN_TABLE),
            [
                'order_payment_id' => $this->order->getPayment()->getEntityId(),
                'payment_token_id' => $paymentToken->getEntityId()
            ]
        );

        static::assertTrue(
            $this->paymentToken->addLinkToOrderPayment(
                $paymentToken->getEntityId(),
                $this->order->getPayment()->getEntityId()
            )
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Braintree/_files/paypal_vault_token.php
     */
    public function testAddLinkToOrderPaymentCreate()
    {
        $this->order->loadByIncrementId(self::ORDER_INCREMENT_ID);
        $paymentToken = $this->paymentTokenManagement
            ->getByGatewayToken(self::TOKEN, PayPalConfigProvider::PAYPAL_CODE, self::CUSTOMER_ID);

        $select = $this->connection->select()
            ->from($this->resource->getTableName(InstallSchema::ORDER_PAYMENT_TO_PAYMENT_TOKEN_TABLE))
            ->where('order_payment_id = ?', (int) $this->order->getPayment()->getEntityId())
            ->where('payment_token_id =?', (int) $paymentToken->getEntityId());

        static::assertEmpty($this->connection->fetchRow($select));
        static::assertTrue(
            $this->paymentToken->addLinkToOrderPayment(
                $paymentToken->getEntityId(),
                $this->order->getPayment()->getEntityId()
            )
        );
        static::assertNotEmpty($this->connection->fetchRow($select));
    }
}
