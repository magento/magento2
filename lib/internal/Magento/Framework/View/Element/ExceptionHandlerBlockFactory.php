<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

/**
 * Class ExceptionHandlerBlockFactory
 * @package Magento\Framework\View\Element
 * @since 2.0.0
 */
class ExceptionHandlerBlockFactory
{
    const DEFAULT_INSTANCE_NAME = \Magento\Framework\View\Element\ExceptionHandlerBlock::class;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $instanceName;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = self::DEFAULT_INSTANCE_NAME
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create exception handling block
     *
     * @param array $data
     * @return \Magento\Framework\View\Element\BlockInterface
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
