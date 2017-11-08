<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type;

use Magento\Framework\Exception\InputException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Create type handler from its name
 */
class HandlerFactory
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
     * Instantiate type handler class
     *
     * @param string $typeClassName
     * @return HandlerInterface
     * @throws InputException
     */
    public function create($typeClassName)
    {
        $typeHandlerClass = $this->objectManager->create($typeClassName);
        if (!($typeHandlerClass instanceof HandlerInterface)) {
            throw new InputException(__('Invalid type name. Type handler does not exist.'));
        }

        return $typeHandlerClass;
    }
}
