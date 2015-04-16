<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task;

class OperationFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Area
     */
    const AREA_CONFIG_GENERATOR = 'area';

    /**
     * Interception
     */
    const INTERCEPTION = 'interception';

    /**
     * Interception cache
     */
    const INTERCEPTION_CACHE = 'interception_cache';

    /**
     * Repository generator
     */
    const REPOSITORY_GENERATOR = 'repository_generator';

    /**
     * Application code generator
     */
    const APPLICATION_CODE_GENERATOR = 'application_code_generator';

    /**
     * Operations definitions
     *
     * @var array
     */
    private $operationsDefinitions = [
        self::AREA_CONFIG_GENERATOR => 'Magento\Setup\Module\Di\App\Task\Operation\Area',
        self::APPLICATION_CODE_GENERATOR => 'Magento\Setup\Module\Di\App\Task\Operation\ApplicationCodeGenerator',
        self::INTERCEPTION => 'Magento\Setup\Module\Di\App\Task\Operation\Interception',
        self::INTERCEPTION_CACHE => 'Magento\Setup\Module\Di\App\Task\Operation\InterceptionCache',
        self::REPOSITORY_GENERATOR => 'Magento\Setup\Module\Di\App\Task\Operation\RepositoryGenerator'
    ];

    /**
     * @param \Magento\Framework\Console\ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(\Magento\Framework\Console\ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManager = $objectManagerProvider->get();
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
