<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\PaymentMethodMapper;

use Magento\Framework\Config\Dom\ValidationSchemaException;

/**
 * Converts XML config file to payment methods mapping.
 * @since 2.2.0
 */
class XmlToArrayConfigConverter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Node type wrapper for magento and signifyd payment codes
     *
     * @var string
     * @since 2.2.0
     */
    private static $paymentMethodNodeType = 'payment_method';

    /**
     * Node type for payment methods code
     *
     * @var string
     * @since 2.2.0
     */
    private static $magentoCodeNodeType = 'magento_code';

    /**
     * Node type for Sygnifyd payment methods code
     *
     * @var string
     * @since 2.2.0
     */
    private static $signifydCodeNodeType = 'signifyd_code';

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function convert($source)
    {
        $paymentMethods = $source->getElementsByTagName(self::$paymentMethodNodeType);
        $paymentsList = [];
        foreach ($paymentMethods as $paymentMethod) {
            $paymentsList += $this->getPaymentMethodMapping($paymentMethod);
        }

        return $paymentsList;
    }

    /**
     * Adds a payment method as key and a Sygnifyd payment method as value
     * in the payment list array
     *
     * @param \DOMElement $payment
     * @return array
     * @throws ValidationSchemaException
     * @since 2.2.0
     */
    private function getPaymentMethodMapping(\DOMElement $payment)
    {
        $paymentMethodCode = $this->readSubnodeValue($payment, self::$magentoCodeNodeType);
        $signifyPaymentMethodCode = $this->readSubnodeValue($payment, self::$signifydCodeNodeType);

        return [$paymentMethodCode => $signifyPaymentMethodCode];
    }

    /**
     * Reads node value by node type
     *
     * @param \DOMElement $element
     * @param string      $subNodeType
     * @return mixed
     * @throws ValidationSchemaException
     * @since 2.2.0
     */
    private function readSubnodeValue(\DOMElement $element, $subNodeType)
    {
        $domList = $element->getElementsByTagName($subNodeType);
        if (empty($domList[0])) {
            throw new ValidationSchemaException(__('Only single entrance of "%1" node is required.', $subNodeType));
        }

        $subNodeValue = trim($domList[0]->nodeValue);
        if (!$subNodeValue) {
            throw new ValidationSchemaException(__('Not empty value for "%1" node is required.', $subNodeType));
        }

        return $subNodeValue;
    }
}
