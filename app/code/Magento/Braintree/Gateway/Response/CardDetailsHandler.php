<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Response;

use \Braintree\Transaction;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class CardDetailsHandler
 */
class CardDetailsHandler implements HandlerInterface
{
    const CARD_TYPE = 'cardType';

    const CARD_EXP_MONTH = 'expirationMonth';

    const CARD_EXP_YEAR = 'expirationYear';

    const CARD_LAST4 = 'last4';

    const CARD_NUMBER = 'cc_number';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $transaction = $this->subjectReader->readTransaction($response);

        /**
         * @TODO after changes in sales module should be refactored for new interfaces
         */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $creditCard = $transaction->creditCard;
        $payment->setCcLast4($creditCard[self::CARD_LAST4]);
        $payment->setCcExpMonth($creditCard[self::CARD_EXP_MONTH]);
        $payment->setCcExpYear($creditCard[self::CARD_EXP_YEAR]);

        $payment->setCcType($this->getCreditCardType($creditCard[self::CARD_TYPE]));

        // set card details to additional info
        $payment->setAdditionalInformation(self::CARD_NUMBER, 'xxxx-' . $creditCard[self::CARD_LAST4]);
        $payment->setAdditionalInformation(OrderPaymentInterface::CC_TYPE, $creditCard[self::CARD_TYPE]);
    }

    /**
     * Get type of credit card mapped from Braintree
     *
     * @param string $type
     * @return array
     */
    private function getCreditCardType($type)
    {
        $replaced = str_replace(' ', '-', strtolower($type));
        $mapper = $this->config->getCctypesMapper();

        return $mapper[$replaced];
    }
}
