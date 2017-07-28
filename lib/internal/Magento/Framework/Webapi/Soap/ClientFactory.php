<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Soap;

/**
 * Class ClientFactory
 * @package Magento\Framework\Webapi\Soap
 * @since 2.0.0
 */
class ClientFactory
{
    /**
     * Factory method for \SoapClient
     *
     * @param string $wsdl
     * @param array $options
     * @return \SoapClient
     * @since 2.0.0
     */
    public function create($wsdl, array $options = [])
    {
        return new \SoapClient($wsdl, $options);
    }
}
