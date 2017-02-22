<?php
/**
 * Factory to create new SoapServer objects.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap;

class ServerFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Webapi\Controller\Soap\Request\Handler
     */
    protected $_soapHandler;

    /**
     * Initialize the class
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Webapi\Controller\Soap\Request\Handler $soapHandler
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Webapi\Controller\Soap\Request\Handler $soapHandler
    ) {
        $this->_objectManager = $objectManager;
        $this->_soapHandler = $soapHandler;
    }

    /**
     * Create SoapServer
     *
     * @param string $url URL of a WSDL file
     * @param array $options Options including encoding, soap_version etc
     * @return \SoapServer
     */
    public function create($url, $options)
    {
        $soapServer = $this->_objectManager->create('SoapServer', ['wsdl' => $url, 'options' => $options]);
        $soapServer->setObject($this->_soapHandler);
        return $soapServer;
    }
}
