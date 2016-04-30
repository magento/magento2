<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
