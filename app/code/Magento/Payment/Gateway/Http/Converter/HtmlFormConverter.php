<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Gateway\Http\Converter;

use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;

/**
 * Class HtmlFormConverter
 * @package Magento\Payment\Gateway\Http\Converter
 * @api
 * @since 100.0.2
 */
class HtmlFormConverter implements ConverterInterface
{
    /**
     * Converts gateway response to ENV structure
     *
     * @param string $response
     * @return array
     * @throws ConverterException
     */
    public function convert($response)
    {
        $document = new \DOMDocument();

        libxml_use_internal_errors(true);
        if (!$document->loadHTML($response)) {
            throw new ConverterException(
                __('The gateway response format was incorrect. Verify the format and try again.')
            );
        }
        libxml_use_internal_errors(false);

        $document->getElementsByTagName('input');

        $convertedResponse = [];
        /** @var \DOMNode $inputNode */
        foreach ($document->getElementsByTagName('input') as $inputNode) {
            if (!$inputNode->attributes->getNamedItem('value')
                || !$inputNode->attributes->getNamedItem('name')
            ) {
                continue;
            }
            $convertedResponse[$inputNode->attributes->getNamedItem('name')->nodeValue]
                = $inputNode->attributes->getNamedItem('value')->nodeValue;
        }

        return $convertedResponse;
    }
}
