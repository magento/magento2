<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Factory class for \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
 */
namespace Magento\Quote\Model\Quote\Address;

/**
 * Class \Magento\Quote\Model\Quote\Address\TotalFactory
 *
 * @since 2.0.0
 */
class TotalFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager = null;

    /**
     * Quote address factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objManager)
    {
        $this->_objectManager = $objManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $instanceName
     * @param array $data
     * @return Total\AbstractTotal|Total
     * @since 2.0.0
     */
    public function create($instanceName = Total::class, array $data = [])
    {
        return $this->_objectManager->create($instanceName, $data);
    }
}
