<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Webapi\Soap;

/**
 * Class ClientFactory
 * @package Magento\Framework\Webapi\Soap
 */
class ClientFactory
{
    /**
     * Factory method for \SoapClient
     *
     * @param string $wsdl
     * @param array $options
     * @return \SoapClient
     */
    public function create($wsdl, array $options = [])
    {
        return new \SoapClient($wsdl, $options);
    }
}
