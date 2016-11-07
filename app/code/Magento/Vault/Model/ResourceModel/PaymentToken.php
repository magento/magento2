<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
            ->where('order_payment_id = ?', (int) $paymentId);
        return $connection->fetchRow($select);
    }

    /**
     * Get payment token by gateway token.
     *
     * @param string $token The gateway token.
     * @param string $paymentMethodCode
     * @param int $customerId Customer ID.
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByGatewayToken($token, $paymentMethodCode, $customerId = 0)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable())
            ->where('gateway_token = ?', $token)
            ->where('payment_method_code = ?', $paymentMethodCode);
        if ($customerId > 0) {
            $select = $select->where('customer_id = ?', $customerId);
        } else {
            $select = $select->where('customer_id IS NULL');
        }
        return $connection->fetchRow($select);
    }

    /**
     * Get payment token by public hash.
     *
     * @param string $hash Public hash.
     * @param int $customerId Customer ID.
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByPublicHash($hash, $customerId = 0)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable())
            ->where('public_hash = ?', $hash);
        if ($customerId > 0) {
            $select = $select->where('customer_id = ?', $customerId);
        } else {
            $select = $select->where('customer_id IS NULL');
        }
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

        $select = $connection->select()
            ->from($this->getTable(InstallSchema::ORDER_PAYMENT_TO_PAYMENT_TOKEN_TABLE))
            ->where('order_payment_id = ?', (int) $orderPaymentId)
            ->where('payment_token_id =?', (int) $paymentTokenId);

        if (!empty($connection->fetchRow($select))) {
            return true;
        }

        return 1 === $connection->insert(
            $this->getTable(InstallSchema::ORDER_PAYMENT_TO_PAYMENT_TOKEN_TABLE),
            ['order_payment_id' => (int) $orderPaymentId, 'payment_token_id' => (int) $paymentTokenId]
        );
    }
}
