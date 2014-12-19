<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * Relations
     */
    const RELATIONS = 'relations';

    /**
     * Plugins
     */
    const PLUGINS = 'plugins';

    /**
     * Operations definitions
     *
     * @var array
     */
    private $operationsDefinitions = [
        self::AREA => 'Magento\Tools\Di\App\Task\Operation\Area',
        self::INTERCEPTION => 'Magento\Tools\Di\App\Task\Operation\Interception',
        self::RELATIONS => 'Magento\Tools\Di\App\Task\Operation\Relations',
        self::PLUGINS => 'Magento\Tools\Di\App\Task\Operation\Plugins',
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
            throw new OperationException('Unrecognized operation', OperationException::UNAVAILABLE_OPERATION);
        }
        return $this->objectManager->create($this->operationsDefinitions[$operationAlias], ['data' => $arguments]);
    }
}
