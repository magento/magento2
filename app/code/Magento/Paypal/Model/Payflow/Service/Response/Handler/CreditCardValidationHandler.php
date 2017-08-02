<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response\Handler;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Paypal\Model\Info;

/**
 * Class \Magento\Paypal\Model\Payflow\Service\Response\Handler\CreditCardValidationHandler
 *
 * @since 2.0.0
 */
class CreditCardValidationHandler implements HandlerInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    private $fieldsToHandle = [
        Info::PAYPAL_CVV2MATCH,
        Info::PAYPAL_AVSZIP,
        Info::PAYPAL_AVSADDR,
        Info::PAYPAL_IAVS
    ];

    /**
     * @var Info
     * @since 2.0.0
     */
    private $paypalInfoManager;

    /**
     * @param Info $paypalInfoManager
     * @since 2.0.0
     */
    public function __construct(Info $paypalInfoManager)
    {
        $this->paypalInfoManager = $paypalInfoManager;
    }

    /**
     * {inheritdoc}
     * @since 2.0.0
     */
    public function handle(InfoInterface $payment, DataObject $response)
    {
        $importObject = [];
        foreach ($this->fieldsToHandle as $field) {
            if ($response->getData($field)) {
                $importObject[$field] = $response->getData($field);
            }
        }

        $this->paypalInfoManager->importToPayment($importObject, $payment);
    }
}
