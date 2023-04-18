<?php
/**
 * Factory to create new SoapServer objects.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap;

use Magento\Framework\ObjectManagerInterface;
use Magento\Webapi\Controller\Soap\Request\Handler;
use SoapServer;

class ServerFactory
{
    /**
     * @var ObjectManagerInterface
     * @deprecated 100.1.0
     */
    protected $_objectManager;

    /**
     * @var Handler
     */
    protected $_soapHandler;

    /**
     * Initialize the class
     *
     * @param ObjectManagerInterface $objectManager
     * @param Handler $soapHandler
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Handler $soapHandler
    ) {
        $this->_objectManager = $objectManager;
        $this->_soapHandler = $soapHandler;
    }

    /**
     * Create SoapServer
     *
     * @param string $url URL of a WSDL file
     * @param array $options Options including encoding, soap_version etc
     * @return SoapServer
     */
    public function create($url, $options)
    {
        $soapServer = new SoapServer($url, $options);
        $soapServer->setObject($this->_soapHandler);
        return $soapServer;
    }
}
