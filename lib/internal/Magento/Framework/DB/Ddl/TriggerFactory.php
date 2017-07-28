<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Ddl;

/**
 * Class \Magento\Framework\DB\Ddl\TriggerFactory
 *
 * @since 2.0.0
 */
class TriggerFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var string
     */
    const INSTANCE_NAME = \Magento\Framework\DB\Ddl\Trigger::class;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\DB\Ddl\Trigger
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(self::INSTANCE_NAME, $data);
    }
}
