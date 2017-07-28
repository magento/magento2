<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ContextFactory
 * @since 2.0.0
 */
class ContextFactory
{
    const INSTANCE_NAME = \Magento\Framework\View\Element\UiComponent\ContextInterface::class;

    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create context
     *
     * @param array $arguments
     * @return ContextInterface
     * @since 2.0.0
     */
    public function create(array $arguments = [])
    {
        return $this->objectManager->create(static::INSTANCE_NAME, $arguments);
    }
}
