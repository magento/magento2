<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Api;

use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Gateway vault payment token repository interface.
 *
 * @api
 */
interface PaymentTokenManagementInterface
{
    /**
     * Lists payment tokens that match specified search criteria.
     *
     * @param int $customerId Customer ID.
     * @return \Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface Payment token search result interface.
     */
    public function getListByCustomerId($customerId);

    /**
     * Get payment token by token Id.
     *
     * @param int $paymentId The gateway payment token ID.
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface Payment token interface.
     */
    public function getByPaymentId($paymentId);

    /**
     * Get payment token by gateway token Id.
     *
     * @param int $customerId Customer ID.
     * @param string $token The gateway token.
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface Payment token interface.
     */
    public function getByGatewayToken($customerId, $token);

    /**
     * @param PaymentTokenInterface $token
     * @param Payment $payment
     * @return bool
     */
    public function saveTokenWithPaymentLink(PaymentTokenInterface $token, Payment $payment);
}
