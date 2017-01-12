<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Gateway\Payflowpro\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Paypal\Model\Payflow\Transparent;

class SaleCommand implements CommandInterface
{
    use Formatter;

    /**
     * @var Transparent
     */
    private $payflowFacade;

    /**
     * SaleCommand constructor.
     * @param Transparent $payflowFacade
     */
    public function __construct(
        Transparent $payflowFacade
    ) {
        $this->payflowFacade = $payflowFacade;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return null|ResultInterface
     */
    public function execute(array $commandSubject)
    {
        /** @var double $amount */
        $amount = $commandSubject['amount'];
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $commandSubject['payment'];
        $payment = $paymentDO->getPayment();

        $storeId = $paymentDO->getOrder()->getStoreId();
        $this->payflowFacade->setStore($storeId);

        /** @var \Magento\Vault\Api\Data\PaymentTokenInterface $token */
        $token = $payment->getExtensionAttributes()->getVaultPaymentToken();

        $request = $this->payflowFacade->buildBasicRequest();
        $request->setAmt($this->formatPrice($amount));
        $request->setTrxtype(Transparent::TRXTYPE_SALE);
        $request->setOrigid($token->getGatewayToken());

        $this->payflowFacade->addRequestOrderInfo($request, $payment->getOrder());

        $response = $this->payflowFacade->postRequest($request, $this->payflowFacade->getConfig());
        $this->payflowFacade->processErrors($response);
        $this->payflowFacade->setTransStatus($payment, $response);
    }
}
