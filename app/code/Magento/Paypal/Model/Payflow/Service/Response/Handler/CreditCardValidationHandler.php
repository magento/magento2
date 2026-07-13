<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response\Handler;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Paypal\Model\Info;

class CreditCardValidationHandler implements HandlerInterface
{
    /**
     * @var array
     */
    private $fieldsToHandle = [
        Info::PAYPAL_CVV2MATCH,
        Info::PAYPAL_AVSZIP,
        Info::PAYPAL_AVSADDR,
        Info::PAYPAL_IAVS
    ];

    /**
     * @var Info
     */
    private $paypalInfoManager;

    /**
     * @param Info $paypalInfoManager
     */
    public function __construct(Info $paypalInfoManager)
    {
        $this->paypalInfoManager = $paypalInfoManager;
    }

    /**
     * @inheritDoc
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
