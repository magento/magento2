<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System configuration clone model factory
 */
namespace Magento\Config\Model\Config\BackendClone;

/**
 * @api
 * @since 100.0.2
 */
class Factory
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
     * Create new clone model
     *
     * @param string $cloneModel
     * @return mixed
     */
    public function create($cloneModel)
    {
        return $this->_objectManager->create($cloneModel);
    }
}
