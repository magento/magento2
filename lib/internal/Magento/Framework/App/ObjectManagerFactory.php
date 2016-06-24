<?php
/**
 * Initialize application object manager.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\Definition\Compiled\Serialized;
use Magento\Framework\App\ObjectManager\Environment;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Code\GeneratedFiles;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * Class ObjectManagerFactory
 */
class ObjectManagerFactory
{
    /**
     * Path to definitions format in deployment configuration
     */
    const CONFIG_PATH_DEFINITION_FORMAT = 'definition/format';

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
    protected $envFactoryClassName = 'Magento\Framework\App\EnvironmentFactory';

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
     * Configuration file pool
     *
     * @var ConfigFilePool
     */
    protected $configFilePool;

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
     * @param ConfigFilePool $configFilePool
     */
    public function __construct(DirectoryList $directoryList, DriverPool $driverPool, ConfigFilePool $configFilePool)
    {
        $this->directoryList = $directoryList;
        $this->driverPool = $driverPool;
        $this->configFilePool = $configFilePool;
    }

    /**
     * Create ObjectManager
     *
     * @param array $arguments
     * @return \Magento\Framework\ObjectManagerInterface
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function create(array $arguments)
    {
        $writeFactory = new \Magento\Framework\Filesystem\Directory\WriteFactory($this->driverPool);
        $generatedFiles = new GeneratedFiles($this->directoryList, $writeFactory);
        $generatedFiles->cleanGeneratedFiles();

        $deploymentConfig = $this->createDeploymentConfig($this->directoryList, $this->configFilePool, $arguments);
        $arguments = array_merge($deploymentConfig->get(), $arguments);
        $definitionFactory = new \Magento\Framework\ObjectManager\DefinitionFactory(
            $this->driverPool->getDriver(DriverPool::FILE),
            $this->directoryList->getPath(DirectoryList::DI),
            $this->directoryList->getPath(DirectoryList::GENERATION),
            $deploymentConfig->get(self::CONFIG_PATH_DEFINITION_FORMAT, Serialized::MODE_NAME)
        );

        $definitions = $definitionFactory->createClassDefinition($deploymentConfig->get('definitions'));
        $relations = $definitionFactory->createRelations();

        /** @var EnvironmentFactory $envFactory */
        $envFactory = new $this->envFactoryClassName($relations, $definitions);
        /** @var EnvironmentInterface $env */
        $env =  $envFactory->createEnvironment();

        /** @var ConfigInterface $diConfig */
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

        // set cache profiler decorator if enabled
        if (\Magento\Framework\Profiler::isEnabled()) {
            $cacheFactoryArguments = $diConfig->getArguments('Magento\Framework\App\Cache\Frontend\Factory');
            $cacheFactoryArguments['decorators'][] = [
                'class' => 'Magento\Framework\Cache\Frontend\Decorator\Profiler',
                'parameters' => ['backendPrefixes' => ['Zend_Cache_Backend_', 'Cm_Cache_Backend_']],
            ];
            $cacheFactoryConfig = [
                'Magento\Framework\App\Cache\Frontend\Factory' => ['arguments' => $cacheFactoryArguments]
            ];
            $diConfig->extend($cacheFactoryConfig);
        }

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
            'Magento\Framework\ObjectManager\ConfigLoaderInterface' => $env->getObjectManagerConfigLoader(),
            $this->_configClassName => $diConfig,
        ];
        $arguments['shared_instances'] = &$sharedInstances;
        $this->factory = $env->getObjectManagerFactory($arguments);

        /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
        $objectManager = new $this->_locatorClassName($this->factory, $diConfig, $sharedInstances);

        $this->factory->setObjectManager($objectManager);
        ObjectManager::setInstance($objectManager);

        $generatorParams = $diConfig->getArguments('Magento\Framework\Code\Generator');
        /** Arguments are stored in different format when DI config is compiled, thus require custom processing */
        $generatedEntities = isset($generatorParams['generatedEntities']['_v_'])
            ? $generatorParams['generatedEntities']['_v_']
            : (isset($generatorParams['generatedEntities']) ? $generatorParams['generatedEntities'] : []);
        $definitionFactory->getCodeGenerator()
            ->setObjectManager($objectManager)
            ->setGeneratedEntities($generatedEntities);

        $env->configureObjectManager($diConfig, $sharedInstances);

        return $objectManager;
    }

    /**
     * Creates deployment configuration object
     *
     * @param DirectoryList $directoryList
     * @param ConfigFilePool $configFilePool
     * @param array $arguments
     * @return DeploymentConfig
     */
    protected function createDeploymentConfig(
        DirectoryList $directoryList,
        ConfigFilePool $configFilePool,
        array $arguments
    ) {
        $customFile = isset($arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG_FILE])
            ? $arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG_FILE]
            : null;
        $customData = isset($arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG])
            ? $arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG]
            : [];
        $reader = new DeploymentConfig\Reader($directoryList, $this->driverPool, $configFilePool, $customFile);
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
                'string' => new \Magento\Framework\Data\Argument\Interpreter\StringUtils($booleanUtils),
                'number' => new \Magento\Framework\Data\Argument\Interpreter\Number(),
                'null' => new \Magento\Framework\Data\Argument\Interpreter\NullType(),
                'object' => new \Magento\Framework\Data\Argument\Interpreter\DataObject($booleanUtils),
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
     * @throws \Magento\Framework\Exception\State\InitException
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
                new \Magento\Framework\Config\FileIteratorFactory(
                    new \Magento\Framework\Filesystem\File\ReadFactory(new \Magento\Framework\Filesystem\DriverPool())
                )
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
            throw new \Magento\Framework\Exception\State\InitException(
                new \Magento\Framework\Phrase($e->getMessage()),
                $e
            );
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
