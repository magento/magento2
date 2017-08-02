<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System configuration comment model factory
 */
namespace Magento\Config\Model\Config;

/**
 * @api
 * @since 2.0.0
 */
class CommentFactory
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
     * Create new config object
     *
     * @param string $type
     * @return CommentInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function create($type)
    {
        $commentModel = $this->_objectManager->create($type);
        if (!$commentModel instanceof CommentInterface) {
            throw new \InvalidArgumentException('Incorrect comment model provided');
        }
        return $commentModel;
    }
}
