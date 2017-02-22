<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

/**
 * Class ExceptionHandlerBlockFactory
 * @package Magento\Framework\View\Element
 */
class ExceptionHandlerBlockFactory
{
    const DEFAULT_INSTANCE_NAME = 'Magento\Framework\View\Element\ExceptionHandlerBlock';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $instanceName;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
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
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
