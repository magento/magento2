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
 * @since 2.0.0
 */
class Factory
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
     * Create new clone model
     *
     * @param string $cloneModel
     * @return mixed
     * @since 2.0.0
     */
    public function create($cloneModel)
    {
        return $this->_objectManager->create($cloneModel);
    }
}
