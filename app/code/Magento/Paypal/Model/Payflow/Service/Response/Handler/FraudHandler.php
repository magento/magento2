<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response\Handler;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Paypal\Model\Info;
use Magento\Paypal\Model\Payflowpro;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Xml\Security;

/**
 * Class FraudHandler
 */
class FraudHandler implements HandlerInterface
{
    /**
     * Response message code
     */
    const RESPONSE_MESSAGE = 'respmsg';

    /**
     * Fraud rules xml code
     */
    const FRAUD_RULES_XML = 'fps_prexmldata';

    /**
     * @var Info
     */
    private $paypalInfoManager;

    /**
     * The security scanner XML document
     *
     * @var Security
     */
    private $xmlSecurity;

    /**
     * Constructor
     *
     * @param Info $paypalInfoManager
     * @param Security $xmlSecurity
     */
    public function __construct(Info $paypalInfoManager, Security $xmlSecurity)
    {
        $this->paypalInfoManager = $paypalInfoManager;
        $this->xmlSecurity = $xmlSecurity;
    }

    /**
     * {inheritdoc}
     */
    public function handle(InfoInterface $payment, DataObject $response)
    {
        if (
        !in_array(
            $response->getData('result'),
            [
                Payflowpro::RESPONSE_CODE_DECLINED_BY_FILTER,
                Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER
            ]
        )) {
            return;
        }

        $fraudMessages = ['RESPMSG' => $response->getData(self::RESPONSE_MESSAGE)];
        if ($response->getData(self::FRAUD_RULES_XML)) {
            $fraudMessages = array_merge(
                $fraudMessages,
                $this->getFraudRulesDictionary($response->getData(self::FRAUD_RULES_XML))
            );
        }

        $this->paypalInfoManager->importToPayment(
            [
                Info::FRAUD_FILTERS =>
                array_merge(
                    $fraudMessages,
                    (array)$payment->getAdditionalInformation(Info::FRAUD_FILTERS)
                )
            ],
            $payment
        );
    }

    /**
     * Converts rules xml document to description=>message dictionary
     *
     * @param string $rulesString
     * @return array
     * @throws LocalizedException
     */
    private function getFraudRulesDictionary($rulesString)
    {
        $rules = [];

        if (!$this->xmlSecurity->scan($rulesString)) {
            return $rules;
        }

        try {
            $rulesXml = new \SimpleXMLElement($rulesString);
            foreach ($rulesXml->{'rule'} as $rule) {
                $rules[(string)$rule->{'ruleDescription'}] = (string)$rule->{'triggeredMessage'};
            }
        } catch (\Exception $e) {

        } finally {
            libxml_use_internal_errors(false);
        }

        return $rules;
    }
}
