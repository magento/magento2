<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Payment\Gateway\Http\Converter\Soap;

use Magento\Payment\Gateway\Http\ConverterInterface;

/**
 * Class ObjectToArrayConverter
 * @package Magento\Payment\Gateway\Http\Converter\Soap
 * @api
 * @since 100.0.2
 */
class ObjectToArrayConverter implements ConverterInterface
{
    /**
     * Converts gateway response to ENV structure
     *
     * @param mixed $response
     * @return array
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function convert($response)
    {
        $response = (array) $response;
        foreach ($response as $key => $value) {
            if (is_object($value)) {
                $response[$key] = $this->convert($value);
            }
        }

        return $response;
    }
}
