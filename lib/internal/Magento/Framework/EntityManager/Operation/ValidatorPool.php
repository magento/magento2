<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ValidatorPool
 * @since 2.1.0
 */
class ValidatorPool
{
    /**
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    protected $objectManager;

    /**
     * @var object[]
     * @since 2.1.0
     */
    protected $validators;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $extensionActions
     * @since 2.1.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $extensionActions = []
    ) {
        $this->objectManager = $objectManager;
        $this->actions = $extensionActions;
    }

    /**
     * @param string $entityType
     * @param string $actionName
     * @return object[]
     * @throws \Exception
     * @since 2.1.0
     */
    public function getValidators($entityType, $actionName)
    {
        $actions = [];
        foreach ($this->validators as $name => $actionGroup) {
            if (isset($actionGroup[$entityType][$actionName])) {
                $actions[$name] = $this->objectManager->get($actionGroup[$entityType][$actionName]);
            } elseif (isset($actionGroup['default'][$actionName])) {
                $actions[$name] = $this->objectManager->get($actionGroup['default'][$actionName]);
            }
        }
        return $actions;
    }
}
