<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\UiComponent\LayoutInterface;

/**
 * Class Pool
 * @since 2.0.0
 */
class Pool
{
    const DEFAULT_CLASS = \Magento\Framework\View\Layout\Generic::class;

    /**
     * Layouts pool
     *
     * @var array
     * @since 2.0.0
     */
    protected $types;

    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $types
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $types = []
    ) {
        $this->objectManager = $objectManager;
        $this->types = $types;
    }

    /**
     * Get layout by type
     *
     * @param string $layoutType
     * @param array $arguments
     * @return LayoutInterface
     * @since 2.0.0
     */
    public function create($layoutType, array $arguments = [])
    {
        if (!isset($this->types[$layoutType])) {
            throw new \InvalidArgumentException(sprintf('Unknown layout type "%s"', $layoutType));
        }
        $defArgs = $this->types[$layoutType];
        $class = isset($defArgs['class']) ? $defArgs['class'] : self::DEFAULT_CLASS;
        unset($defArgs['class']);
        if ($defArgs) {
            $arguments = array_merge($defArgs, $arguments);
        }
        return $this->objectManager->create($class, $arguments);
    }
}
