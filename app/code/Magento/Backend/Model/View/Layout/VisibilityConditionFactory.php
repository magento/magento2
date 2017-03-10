<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout;

use Magento\Framework\ObjectManagerInterface;

/**
 * Creates visibility condition classes.
 */
class VisibilityConditionFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $conditionClassName
     *
     * @return VisibilityConditionInterface
     */
    public function create($conditionClassName)
    {
        return $this->objectManager->create($conditionClassName);
    }
}
