<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task;

/**
 * Class \Magento\Setup\Module\Di\App\Task\OperationFactory
 *
 * @since 2.0.0
 */
class OperationFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
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
     * Proxy generator
     */
    const PROXY_GENERATOR = 'proxy_generator';

    /**
     * Service data attributes generator
     */
    const DATA_ATTRIBUTES_GENERATOR = 'extension_attributes_generator';

    /**
     * Application code generator
     */
    const APPLICATION_CODE_GENERATOR = 'application_code_generator';

    /**
     * Operations definitions
     *
     * @var array
     * @since 2.0.0
     */
    private $operationsDefinitions = [
        self::DATA_ATTRIBUTES_GENERATOR =>
            \Magento\Setup\Module\Di\App\Task\Operation\ServiceDataAttributesGenerator::class,
        self::AREA_CONFIG_GENERATOR => \Magento\Setup\Module\Di\App\Task\Operation\Area::class,
        self::APPLICATION_CODE_GENERATOR => \Magento\Setup\Module\Di\App\Task\Operation\ApplicationCodeGenerator::class,
        self::INTERCEPTION => \Magento\Setup\Module\Di\App\Task\Operation\Interception::class,
        self::INTERCEPTION_CACHE => \Magento\Setup\Module\Di\App\Task\Operation\InterceptionCache::class,
        self::REPOSITORY_GENERATOR => \Magento\Setup\Module\Di\App\Task\Operation\RepositoryGenerator::class,
        self::PROXY_GENERATOR => \Magento\Setup\Module\Di\App\Task\Operation\ProxyGenerator::class,
    ];

    /**
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     * @since 2.0.0
     */
    public function __construct(\Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider)
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
     * @since 2.0.0
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
