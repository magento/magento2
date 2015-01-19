<?php
/**
 * Initialize application object manager.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Code\Generator\FileResolver;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\ObjectManager\Environment;
use Magento\Framework\ObjectManager\EnvironmentFactory;
use Magento\Framework\ObjectManager\EnvironmentInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * Class ObjectManagerFactory
 */
class ObjectManagerFactory
{
    /**
     * Initialization parameter for a custom deployment configuration file
     */
    const INIT_PARAM_DEPLOYMENT_CONFIG_FILE = 'MAGE_CONFIG_FILE';

    /**
     * Initialization parameter for custom deployment configuration data
     */
    const INIT_PARAM_DEPLOYMENT_CONFIG = 'MAGE_CONFIG';

    /**
     * Locator class name
     *
     * @var string
     */
    protected $_locatorClassName = 'Magento\Framework\ObjectManager\ObjectManager';

    /**
     * Config class name
     *
     * @var string
     */
    protected $_configClassName = 'Magento\Framework\Interception\ObjectManager\ConfigInterface';

    /**
     * Environment factory class name
     *
     * @var string
     */
    protected $envFactoryClassName = 'Magento\Framework\ObjectManager\EnvironmentFactory';

    /**
     * Filesystem directory list
     *
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * Filesystem driver pool
     *
     * @var DriverPool
     */
    protected $driverPool;

    /**
     * Factory
     *
     * @var \Magento\Framework\ObjectManager\FactoryInterface
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     * @param DriverPool $driverPool
     */
    public function __construct(DirectoryList $directoryList, DriverPool $driverPool)
    {
        $this->directoryList = $directoryList;
        $this->driverPool = $driverPool;
    }

    /**
     * Create ObjectManager
     *
     * @param array $arguments
     * @param bool $useCompiled
     * @return \Magento\Framework\ObjectManagerInterface
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function create(array $arguments, $useCompiled = true)
    {
        $deploymentConfig = $this->createDeploymentConfig($this->directoryList, $arguments);

        $definitionFactory = new \Magento\Framework\ObjectManager\DefinitionFactory(
            $this->driverPool->getDriver(DriverPool::FILE),
            $this->directoryList->getPath(DirectoryList::DI),
            $this->directoryList->getPath(DirectoryList::GENERATION),
            $deploymentConfig->get('definition/format', 'serialized')
        );

        $definitions = $definitionFactory->createClassDefinition($deploymentConfig->get('definitions'), $useCompiled);
        $relations = $definitionFactory->createRelations();

        /** @var \Magento\Framework\ObjectManager\EnvironmentFactory $enFactory */
        $enFactory = new $this->envFactoryClassName($relations, $definitions);
        /** @var EnvironmentInterface $env */
        $env =  $enFactory->createEnvironment();

        /** @var \Magento\Framework\Interception\ObjectManager\ConfigInterface $diConfig */
        $diConfig = $env->getDiConfig();

        $appMode = isset($arguments[State::PARAM_MODE]) ? $arguments[State::PARAM_MODE] : State::MODE_DEFAULT;
        $booleanUtils = new \Magento\Framework\Stdlib\BooleanUtils();
        $argInterpreter = $this->createArgumentInterpreter($booleanUtils);
        $argumentMapper = new \Magento\Framework\ObjectManager\Config\Mapper\Dom($argInterpreter);

        if ($env->getMode() != Environment\Compiled::MODE) {
            $configData = $this->_loadPrimaryConfig($this->directoryList, $this->driverPool, $argumentMapper, $appMode);
            if ($configData) {
                $diConfig->extend($configData);
            }
        }

        $this->factory = $env->getObjectManagerFactory($arguments);

        $sharedInstances = [
            'Magento\Framework\App\DeploymentConfig' => $deploymentConfig,
            'Magento\Framework\App\Filesystem\DirectoryList' => $this->directoryList,
            'Magento\Framework\Filesystem\DirectoryList' => $this->directoryList,
            'Magento\Framework\Filesystem\DriverPool' => $this->driverPool,
            'Magento\Framework\ObjectManager\RelationsInterface' => $relations,
            'Magento\Framework\Interception\DefinitionInterface' => $definitionFactory->createPluginDefinition(),
            'Magento\Framework\ObjectManager\ConfigInterface' => $diConfig,
            'Magento\Framework\Interception\ObjectManager\ConfigInterface' => $diConfig,
            'Magento\Framework\ObjectManager\DefinitionInterface' => $definitions,
            'Magento\Framework\Stdlib\BooleanUtils' => $booleanUtils,
            'Magento\Framework\ObjectManager\Config\Mapper\Dom' => $argumentMapper,
            'Magento\Framework\App\ObjectManager\ConfigLoader' => $env->getObjectManagerConfigLoader(),
            $this->_configClassName => $diConfig,
        ];

        /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
        $objectManager = new $this->_locatorClassName($this->factory, $diConfig, $sharedInstances);

        $this->factory->setObjectManager($objectManager);
        ObjectManager::setInstance($objectManager);

        $diConfig->setCache(
            $objectManager->get('Magento\Framework\App\ObjectManager\ConfigCache')
        );

        $objectManager->configure(
            $objectManager->get('Magento\Framework\App\ObjectManager\ConfigLoader')->load('global')
        );
        $objectManager->get('Magento\Framework\Config\ScopeInterface')
            ->setCurrentScope('global');
        $diConfig->setInterceptionConfig(
            $objectManager->get('Magento\Framework\Interception\Config\Config')
        );
        return $objectManager;
    }

    /**
     * Creates deployment configuration object
     *
     * @param DirectoryList $directoryList
     * @param array $arguments
     * @return DeploymentConfig
     */
    protected function createDeploymentConfig(DirectoryList $directoryList, array $arguments)
    {
        $customFile = isset($arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG_FILE])
            ? $arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG_FILE]
            : null;
        $customData = isset($arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG])
            ? $arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG]
            : [];
        $reader = new DeploymentConfig\Reader($directoryList, $customFile);
        return new DeploymentConfig($reader, $customData);
    }

    /**
     * Return newly created instance on an argument interpreter, suitable for processing DI arguments
     *
     * @param \Magento\Framework\Stdlib\BooleanUtils $booleanUtils
     * @return \Magento\Framework\Data\Argument\InterpreterInterface
     */
    protected function createArgumentInterpreter(
        \Magento\Framework\Stdlib\BooleanUtils $booleanUtils
    ) {
        $constInterpreter = new \Magento\Framework\Data\Argument\Interpreter\Constant();
        $result = new \Magento\Framework\Data\Argument\Interpreter\Composite(
            [
                'boolean' => new \Magento\Framework\Data\Argument\Interpreter\Boolean($booleanUtils),
                'string' => new \Magento\Framework\Data\Argument\Interpreter\String($booleanUtils),
                'number' => new \Magento\Framework\Data\Argument\Interpreter\Number(),
                'null' => new \Magento\Framework\Data\Argument\Interpreter\NullType(),
                'object' => new \Magento\Framework\Data\Argument\Interpreter\Object($booleanUtils),
                'const' => $constInterpreter,
                'init_parameter' => new \Magento\Framework\App\Arguments\ArgumentInterpreter($constInterpreter),
            ],
            \Magento\Framework\ObjectManager\Config\Reader\Dom::TYPE_ATTRIBUTE
        );
        // Add interpreters that reference the composite
        $result->addInterpreter('array', new \Magento\Framework\Data\Argument\Interpreter\ArrayType($result));
        return $result;
    }

    /**
     * Load primary config
     *
     * @param DirectoryList $directoryList
     * @param DriverPool $driverPool
     * @param mixed $argumentMapper
     * @param string $appMode
     * @return array
     * @throws \Magento\Framework\App\InitException
     */
    protected function _loadPrimaryConfig(DirectoryList $directoryList, $driverPool, $argumentMapper, $appMode)
    {
        $configData = null;
        try {
            $fileResolver = new \Magento\Framework\App\Arguments\FileResolver\Primary(
                new \Magento\Framework\Filesystem(
                    $directoryList,
                    new \Magento\Framework\Filesystem\Directory\ReadFactory($driverPool),
                    new \Magento\Framework\Filesystem\Directory\WriteFactory($driverPool)
                ),
                new \Magento\Framework\Config\FileIteratorFactory()
            );
            $schemaLocator = new \Magento\Framework\ObjectManager\Config\SchemaLocator();
            $validationState = new \Magento\Framework\App\Arguments\ValidationState($appMode);

            $reader = new \Magento\Framework\ObjectManager\Config\Reader\Dom(
                $fileResolver,
                $argumentMapper,
                $schemaLocator,
                $validationState
            );
            $configData = $reader->read('primary');
        } catch (\Exception $e) {
            throw new \Magento\Framework\App\InitException($e->getMessage(), $e->getCode(), $e);
        }
        return $configData;
    }

    /**
     * Crete plugin list object
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\ObjectManager\RelationsInterface $relations
     * @param \Magento\Framework\ObjectManager\DefinitionFactory $definitionFactory
     * @param \Magento\Framework\ObjectManager\Config\Config $diConfig
     * @param \Magento\Framework\ObjectManager\DefinitionInterface $definitions
     * @return \Magento\Framework\Interception\PluginList\PluginList
     */
    protected function _createPluginList(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\ObjectManager\RelationsInterface $relations,
        \Magento\Framework\ObjectManager\DefinitionFactory $definitionFactory,
        \Magento\Framework\ObjectManager\Config\Config $diConfig,
        \Magento\Framework\ObjectManager\DefinitionInterface $definitions
    ) {
        return $objectManager->create(
            'Magento\Framework\Interception\PluginList\PluginList',
            [
                'relations' => $relations,
                'definitions' => $definitionFactory->createPluginDefinition(),
                'omConfig' => $diConfig,
                'classDefinitions' => $definitions instanceof
                \Magento\Framework\ObjectManager\Definition\Compiled ? $definitions : null
            ]
        );
    }
}
