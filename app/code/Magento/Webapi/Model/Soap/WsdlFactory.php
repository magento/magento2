<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory of WSDL builders.
 */
class WsdlFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create WSDL builder instance.
     *
     * @param string $wsdlName
     * @param string $endpointUrl
     * @return Wsdl
     */
    public function create($wsdlName, $endpointUrl)
    {
        return $this->_objectManager->create(
            Wsdl::class,
            ['name' => $wsdlName, 'uri' => $endpointUrl]
        );
    }
}
