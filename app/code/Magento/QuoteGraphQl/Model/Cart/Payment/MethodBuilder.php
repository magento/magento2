<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Payment;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\PaymentInterfaceFactory;

class MethodBuilder
{
    /**
     * @var PaymentInterfaceFactory
     */
    private $paymentFactory;

    /**
     * @var AdditionalDataBuilderPool
     */
    private $additionalDataBuilderPool;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param PaymentInterfaceFactory $paymentFactory
     * @param AdditionalDataBuilderPool $additionalDataBuilderPool
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        PaymentInterfaceFactory $paymentFactory,
        AdditionalDataBuilderPool $additionalDataBuilderPool,
        ArrayManager $arrayManager
    ) {
        $this->paymentFactory = $paymentFactory;
        $this->additionalDataBuilderPool = $additionalDataBuilderPool;
        $this->arrayManager = $arrayManager;
    }

    public function build(array $args): PaymentInterface
    {
        $method = (string) $this->arrayManager->get('input/payment_method/method', $args);

        return $this->paymentFactory->create([
            'data' => [
                PaymentInterface::KEY_METHOD => $method,
                PaymentInterface::KEY_PO_NUMBER => $this->arrayManager->get('input/payment_method/po_number', $args),
                PaymentInterface::KEY_ADDITIONAL_DATA => $this->additionalDataBuilderPool->buildForMethod(
                    $method,
                    $args
                ),
            ]
        ]);
    }
}
