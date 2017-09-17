<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Model;

/**
 * @api
 * @since 100.0.2
 */
class ActionFactory
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
     * Create new action object
     *
     * @param string $type
     * @param array $data
     * @return \Magento\Rule\Model\Action\ActionInterface
     */
    public function create($type, array $data = [])
    {
        return $this->_objectManager->create($type, $data);
    }
}
