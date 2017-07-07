<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Control;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ActionPoolFactory
 */
class ActionPoolFactory
{
    const INSTANCE = \Magento\Framework\View\Element\UiComponent\Control\ActionPoolInterface::class;

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
     * Create Action Pool
     *
     * @param array $arguments
     * @return ActionPoolInterface
     */
    public function create(array $arguments = [])
    {
        return $this->objectManager->create(static::INSTANCE, $arguments);
    }
}
