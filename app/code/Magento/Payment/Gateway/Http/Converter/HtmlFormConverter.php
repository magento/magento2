<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Http\Converter;

use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;

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
            throw new ConverterException(__('Wrong gateway response format.'));
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
