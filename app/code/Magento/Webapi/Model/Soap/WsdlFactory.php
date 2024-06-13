<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Webapi\Model\Soap;

/**
 * Factory of WSDL builders.
 */
class WsdlFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create WSDL builder instance.
     *
     * @param string $wsdlName
     * @param string $endpointUrl
     * @return \Magento\Webapi\Model\Soap\Wsdl
     */
    public function create($wsdlName, $endpointUrl)
    {
        return $this->_objectManager->create(
            \Magento\Webapi\Model\Soap\Wsdl::class,
            ['name' => $wsdlName, 'uri' => $endpointUrl]
        );
    }
}
