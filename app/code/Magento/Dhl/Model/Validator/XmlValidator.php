<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Dhl\Model\Validator;

use Magento\Sales\Exception\DocumentValidationException;

/**
 * Validates XML responses from DHL's web service
 */
class XmlValidator
{
    /**
     * @var \Magento\Framework\Xml\Security
     */
    private $xmlSecurity;

    /**
     * @var ResponseErrorProcessor
     */
    private $errorProcessor;

    /**
     * Initialize XmlValidator dependencies
     *
     * @param \Magento\Framework\Xml\Security $xmlSecurity
     * @param ResponseErrorProcessor $errorProcessor
     */
    public function __construct(
        \Magento\Framework\Xml\Security $xmlSecurity,
        ResponseErrorProcessor $errorProcessor
    ) {
        $this->xmlSecurity = $xmlSecurity;
        $this->errorProcessor = $errorProcessor;
    }

    /**
     * Validate DHL XML responses
     *
     * @param string $xmlResponse
     * @param bool $isShippingLabel
     * @return void
     * @throws DocumentValidationException
     */
    public function validate($xmlResponse, $isShippingLabel = false)
    {
        if ($xmlResponse !== null && strlen(trim($xmlResponse)) > 0 && strpos(trim($xmlResponse), '<?xml') === 0) {
            if (!$this->xmlSecurity->scan($xmlResponse)) {
                throw new DocumentValidationException(__('The security validation of the XML document has failed.'));
            }
            $xml = simplexml_load_string($xmlResponse, \Magento\Shipping\Model\Simplexml\Element::class);

            if (in_array($xml->getName(), ['ErrorResponse', 'ShipmentValidateErrorResponse'])
                || isset($xml->GetQuoteResponse->Note->Condition)
            ) {
                /** @var \Magento\Framework\Phrase $exceptionPhrase */
                $exceptionPhrase = $this->errorProcessor->process($xml, $isShippingLabel);
                throw new DocumentValidationException($exceptionPhrase, null, $exceptionPhrase->getArguments()[0]);
            }
        } else {
            throw new DocumentValidationException(__('The response is in the wrong format'));
        }
    }
}
