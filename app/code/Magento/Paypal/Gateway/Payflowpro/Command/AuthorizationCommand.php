<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Gateway\Payflowpro\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Paypal\Model\Payflow\Transparent;

/**
 * Class \Magento\Paypal\Gateway\Payflowpro\Command\AuthorizationCommand
 *
 */
class AuthorizationCommand implements CommandInterface
{
    use Formatter;

    /**
     * @var Transparent
     */
    private $payflowFacade;

    /**
     * AuthorizationCommand constructor.
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
     * @return ResultInterface|null
     * @throws LocalizedException
     * @throws InvalidTransitionException
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

        $request = $this->payflowFacade->buildBasicRequest();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $this->payflowFacade->addRequestOrderInfo($request, $order);
        $request = $this->payflowFacade->fillCustomerContacts($order, $request);

        /** @var \Magento\Vault\Api\Data\PaymentTokenInterface $token */
        $token = $payment->getExtensionAttributes()->getVaultPaymentToken();
        $request->setData('trxtype', Transparent::TRXTYPE_AUTH_ONLY);
        $request->setData('origid', $token->getGatewayToken());
        $request->setData('amt', $this->formatPrice($amount));

        $response = $this->payflowFacade->postRequest($request, $this->payflowFacade->getConfig());
        $this->payflowFacade->processErrors($response);

        try {
            $this->payflowFacade->getResponceValidator()->validate($response, $this->payflowFacade);
        } catch (LocalizedException $exception) {
            $payment->setParentTransactionId($response->getData(Transparent::PNREF));
            $this->payflowFacade->void($payment);
            throw new LocalizedException(__('Error processing payment, please try again later.'));
        }

        $this->payflowFacade->setTransStatus($payment, $response);

        return $this;
    }
}
