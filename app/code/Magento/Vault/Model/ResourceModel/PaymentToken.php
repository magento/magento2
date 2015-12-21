<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\ResourceModel;

use Magento\Vault\Setup\InstallSchema;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

/**
 * Vault Payment Token Resource Model
 */
class PaymentToken extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(InstallSchema::PAYMENT_TOKEN_TABLE, InstallSchema::ID_FILED_NAME);
    }

    /**
     * Get payment token by order payment Id.
     *
     * @param int $paymentId
     * @return array
     */
    public function getByOrderPaymentId($paymentId)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable())
            ->joinInner(
                $this->getTable(InstallSchema::ORDER_PAYMENT_TO_PAYMENT_TOKEN_TABLE),
                'payment_token_id = entity_id',
                null
            )
            ->where('order_payment_id = ?', intval($paymentId));
        return $connection->fetchRow($select);
    }

    /**
     * Get payment token by gateway token.
     *
     * @param int $customerId Customer ID.
     * @param string $token The gateway token.
     * @return array
     */
    public function getByGatewayToken($customerId, $token)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable())
            ->where('customer_id = ?', $customerId, $token)
            ->where('gateway_token = ?', $token);
        return $connection->fetchRow($select);
    }

    /**
     * Add link between payment token and order payment.
     *
     * @param int $paymentTokenId
     * @param int $orderPaymentId
     * @return bool
     */
    public function addLinkToOrderPayment($paymentTokenId, $orderPaymentId)
    {
        $connection = $this->getConnection();
        $insertedRows = $connection->insert(
            $this->getTable(InstallSchema::ORDER_PAYMENT_TO_PAYMENT_TOKEN_TABLE),
            ['order_payment_id' => intval($orderPaymentId), 'payment_token_id' => intval($paymentTokenId)]
        );
        return $insertedRows == 1;
    }
}
