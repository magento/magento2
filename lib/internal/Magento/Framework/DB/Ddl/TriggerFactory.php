<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Ddl;

/**
 * @api
 */
class TriggerFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    const INSTANCE_NAME = \Magento\Framework\DB\Ddl\Trigger::class;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\DB\Ddl\Trigger
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(self::INSTANCE_NAME, $data);
    }
}
