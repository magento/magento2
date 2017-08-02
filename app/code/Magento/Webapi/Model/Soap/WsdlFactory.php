<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap;

/**
 * Factory of WSDL builders.
 * @since 2.0.0
 */
class WsdlFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create($wsdlName, $endpointUrl)
    {
        return $this->_objectManager->create(
            \Magento\Webapi\Model\Soap\Wsdl::class,
            ['name' => $wsdlName, 'uri' => $endpointUrl]
        );
    }
}
