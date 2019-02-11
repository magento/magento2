<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Adds the basic payment information to the request
 */
class AddressDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $result = [
            'transactionRequest' => []
        ];

        if ($billingAddress) {
            $result['transactionRequest']['billTo'] = [
                'firstName' => $billingAddress->getFirstname(),
                'lastName' => $billingAddress->getLastname(),
                'company' => $billingAddress->getCompany() ?? '',
                'address' => $billingAddress->getStreetLine1(),
                'city' => $billingAddress->getCity(),
                'state' => $billingAddress->getRegionCode(),
                'zip' => $billingAddress->getPostcode(),
                'country' => $billingAddress->getCountryId()
            ];
        }

        if ($shippingAddress) {
            $result['transactionRequest']['shipTo'] = [
                'firstName' => $shippingAddress->getFirstname(),
                'lastName' => $shippingAddress->getLastname(),
                'company' => $shippingAddress->getCompany() ?? '',
                'address' => $shippingAddress->getStreetLine1(),
                'city' => $shippingAddress->getCity(),
                'state' => $shippingAddress->getRegionCode(),
                'zip' => $shippingAddress->getPostcode(),
                'country' => $shippingAddress->getCountryId()
            ];
        }

        if ($order->getRemoteIp()) {
            $result['transactionRequest']['customerIP'] = $order->getRemoteIp();
        }

        return $result;
    }
}
