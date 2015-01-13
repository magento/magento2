<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\App\Task;

class OperationFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Area
     */
    const AREA = 'area';

    /**
     * Interception
     */
    const INTERCEPTION = 'interception';

    /**
     * Interception cache
     */
    const INTERCEPTION_CACHE = 'interception_cache';

    /**
     * Operations definitions
     *
     * @var array
     */
    private $operationsDefinitions = [
        self::AREA => 'Magento\Tools\Di\App\Task\Operation\Area',
        self::INTERCEPTION => 'Magento\Tools\Di\App\Task\Operation\Interception',
        self::INTERCEPTION_CACHE => 'Magento\Tools\Di\App\Task\Operation\InterceptionCache',
    ];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates operation
     *
     * @param string $operationAlias
     * @param mixed $arguments
     * @return OperationInterface
     * @throws OperationException
     */
    public function create($operationAlias, $arguments = null)
    {
        if (!array_key_exists($operationAlias, $this->operationsDefinitions)) {
            throw new OperationException(
                sprintf('Unrecognized operation "%s"', $operationAlias),
                OperationException::UNAVAILABLE_OPERATION
            );
        }
        return $this->objectManager->create($this->operationsDefinitions[$operationAlias], ['data' => $arguments]);
    }
}
