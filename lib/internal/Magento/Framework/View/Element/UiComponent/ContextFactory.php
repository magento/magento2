<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ContextFactory
 */
class ContextFactory
{
    const INSTANCE_NAME = \Magento\Framework\View\Element\UiComponent\ContextInterface::class;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
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
     */
    public function create(array $arguments = [])
    {
        return $this->objectManager->create(static::INSTANCE_NAME, $arguments);
    }
}
