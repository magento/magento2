<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework;

/**
 * Class \Magento\Framework\CurrencyFactory
 *
 * @since 2.0.0
 */
class CurrencyFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager = null;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_instanceName = null;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = CurrencyInterface::class
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return CurrencyInterface
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
