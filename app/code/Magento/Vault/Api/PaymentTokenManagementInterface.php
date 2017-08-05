<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Api;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Gateway vault payment token repository interface.
 *
 * @api
 * @since 2.1.0
 * @since 100.1.0
 */
interface PaymentTokenManagementInterface
{
    /**
     * Lists payment tokens that match specified search criteria.
     *
     * @param int $customerId Customer ID.
     * @return \Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface Payment token search result interface.
     * @since 2.1.0
     * @since 100.1.0
     */
    public function getListByCustomerId($customerId);

    /**
     * Get payment token by token Id.
     *
     * @param int $paymentId The gateway payment token ID.
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface Payment token interface.
     * @since 2.1.0
     * @since 100.1.0
     */
    public function getByPaymentId($paymentId);

    /**
     * Get payment token by gateway token.
     *
     * @param string $token The gateway token.
     * @param string $paymentMethodCode
     * @param int $customerId Customer ID.
     * @return PaymentTokenInterface|null Payment token interface.
     * @since 2.1.0
     * @since 100.1.0
     */
    public function getByGatewayToken($token, $paymentMethodCode, $customerId);

    /**
     * Get payment token by public hash.
     *
     * @param string $hash Public hash.
     * @param int $customerId Customer ID.
     * @return PaymentTokenInterface|null Payment token interface.
     * @since 2.1.0
     * @since 100.1.0
     */
    public function getByPublicHash($hash, $customerId);

    /**
     * @param PaymentTokenInterface $token
     * @param OrderPaymentInterface $payment
     * @return bool
     * @since 2.1.0
     * @since 100.1.0
     */
    public function saveTokenWithPaymentLink(PaymentTokenInterface $token, OrderPaymentInterface $payment);

    /**
     * Add link between payment token and order payment.
     *
     * @param int $paymentTokenId Payment token ID.
     * @param int $orderPaymentId Order payment ID.
     * @return bool
     * @since 2.1.0
     * @since 100.1.0
     */
    public function addLinkToOrderPayment($paymentTokenId, $orderPaymentId);
}
