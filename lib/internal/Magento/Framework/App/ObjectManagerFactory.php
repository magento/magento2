<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

use Magento\Framework\App\Arguments\ArgumentInterpreter;
use Magento\Framework\App\Arguments\FileResolver\Primary;
use Magento\Framework\App\Arguments\ValidationState;
use Magento\Framework\Cache\Frontend\Decorator\Profiler as ProfilerDecorator;
use Magento\Framework\App\Cache\Frontend\Factory as CacheFrontendFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\EnvironmentFactory;
use Magento\Framework\App\Filesystem\DirectoryList as AppDirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\Environment;
use Magento\Framework\Code\GeneratedFiles;
use Magento\Framework\Code\Generator;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Data\Argument\Interpreter\ArrayType;
use Magento\Framework\Data\Argument\Interpreter\BaseStringUtils;
use Magento\Framework\Data\Argument\Interpreter\Boolean;
use Magento\Framework\Data\Argument\Interpreter\Composite;
use Magento\Framework\Data\Argument\Interpreter\Constant;
use Magento\Framework\Data\Argument\Interpreter\DataObject;
use Magento\Framework\Data\Argument\Interpreter\NullType;
use Magento\Framework\Data\Argument\Interpreter\Number;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\Exception\State\InitException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\ReadFactory as FileReadFactory;
use Magento\Framework\Interception\DefinitionInterface as InterceptionDefinitionInterface;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\Interception\PluginList\PluginList;
use Magento\Framework\Lock\Backend\FileLock;
use Magento\Framework\ObjectManager\Config\Config as DiConfig;
use Magento\Framework\ObjectManager\Config\Mapper\Dom as DomMapper;
use Magento\Framework\ObjectManager\Config\Reader\Dom as DomReader;
use Magento\Framework\ObjectManager\Config\SchemaLocator;
use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerConfigInterface;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\ObjectManager\DefinitionFactory;
use Magento\Framework\ObjectManager\DefinitionInterface;
use Magento\Framework\ObjectManager\FactoryInterface;
use Magento\Framework\ObjectManager\RelationsInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Profiler;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Initialization of object manager is a complex operation.
 * To abstract away this complexity, this class was introduced.
 * Objects of this class create fully initialized instance of object manager with "global" configuration loaded.
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class ObjectManagerFactory
{
    /**
     * Initialization parameter for a custom deployment configuration file
     */
    public const INIT_PARAM_DEPLOYMENT_CONFIG_FILE = 'MAGE_CONFIG_FILE';

    /**
     * Initialization parameter for custom deployment configuration data
     */
    public const INIT_PARAM_DEPLOYMENT_CONFIG = 'MAGE_CONFIG';

    /**
     * Object manager class name for locating services
     *
     * @var string
     */
    protected $_locatorClassName = ObjectManager::class;

    /**
     * Interception configuration class name
     *
     * @var string
     */
    protected $_configClassName = ConfigInterface::class;

    /**
     * Environment factory class name
     *
     * @var string
     */
    protected $envFactoryClassName = EnvironmentFactory::class;

    /**
     * Filesystem directory list
     *
     * @var AppDirectoryList
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
     * Object manager factory instance
     *
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param AppDirectoryList $directoryList
     * @param DriverPool $driverPool
     * @param ConfigFilePool $configFilePool
     */
    public function __construct(AppDirectoryList $directoryList, DriverPool $driverPool, ConfigFilePool $configFilePool)
    {
        $this->directoryList = $directoryList;
        $this->driverPool = $driverPool;
        $this->configFilePool = $configFilePool;
    }

    /**
     * Create ObjectManager
     *
     * @param array $arguments
     * @return ObjectManagerInterface
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function create(array $arguments)
    {
        $writeFactory = new WriteFactory($this->driverPool);
        /** @var FileDriver $fileDriver */
        $fileDriver = $this->driverPool->getDriver(DriverPool::FILE);
        $lockManager = new FileLock(
            $fileDriver,
            $this->directoryList->getRoot()
        );
        $generatedFiles = new GeneratedFiles($this->directoryList, $writeFactory, $lockManager);
        $generatedFiles->cleanGeneratedFiles();

        $deploymentConfig = $this->createDeploymentConfig($this->directoryList, $this->configFilePool, $arguments);
        $arguments = array_merge($deploymentConfig->get(), $arguments);
        $definitionFactory = new DefinitionFactory(
            $this->driverPool->getDriver(DriverPool::FILE),
            $this->directoryList->getPath(AppDirectoryList::GENERATED_CODE)
        );

        $definitions = $definitionFactory->createClassDefinition();
        $relations = $definitionFactory->createRelations();

        /** @var EnvironmentFactory $envFactory */
        $envFactory = new $this->envFactoryClassName($relations, $definitions);
        /** @var EnvironmentInterface $env */
        $env = $envFactory->createEnvironment();

        /** @var ConfigInterface $diConfig */
        $diConfig = $env->getDiConfig();

        $appMode = isset($arguments[State::PARAM_MODE]) ? $arguments[State::PARAM_MODE] : State::MODE_DEFAULT;
        $booleanUtils = new BooleanUtils();
        $argInterpreter = $this->createArgumentInterpreter($booleanUtils);
        $argumentMapper = new DomMapper($argInterpreter);

        if ($env->getMode() != Environment\Compiled::MODE) {
            $configData = $this->_loadPrimaryConfig($this->directoryList, $this->driverPool, $argumentMapper, $appMode);
            if ($configData) {
                $diConfig->extend($configData);
            }
        }

        // set cache profiler decorator if enabled
        if (Profiler::isEnabled()) {
            $cacheFactoryArguments = $diConfig->getArguments(CacheFrontendFactory::class);
            $cacheFactoryArguments['decorators'][] = [
                'class' => ProfilerDecorator::class,
                'parameters' => ['backendPrefixes' => [
                    'Magento\Framework\Cache\Backend\\',
                    'Magento\Framework\Cache\Frontend\Adapter\Symfony\\',
                    'Cm_Cache_Backend_'
                ]],
            ];
            $cacheFactoryConfig = [
                CacheFrontendFactory::class => ['arguments' => $cacheFactoryArguments]
            ];
            $diConfig->extend($cacheFactoryConfig);
        }

        $sharedInstances = [
            DeploymentConfig::class => $deploymentConfig,
            AppDirectoryList::class => $this->directoryList,
            DirectoryList::class => $this->directoryList,
            DriverPool::class => $this->driverPool,
            RelationsInterface::class => $relations,
            InterceptionDefinitionInterface::class => $definitionFactory->createPluginDefinition(),
            ObjectManagerConfigInterface::class => $diConfig,
            ConfigInterface::class => $diConfig,
            DefinitionInterface::class => $definitions,
            BooleanUtils::class => $booleanUtils,
            DomMapper::class => $argumentMapper,
            ConfigLoaderInterface::class => $env->getObjectManagerConfigLoader(),
            $this->_configClassName => $diConfig,
        ];
        $arguments['shared_instances'] = &$sharedInstances;
        $this->factory = $env->getObjectManagerFactory($arguments);

        /** @var ObjectManagerInterface $objectManager */
        $objectManager = new $this->_locatorClassName($this->factory, $diConfig, $sharedInstances);

        $this->factory->setObjectManager($objectManager);

        $generatorParams = $diConfig->getArguments(Generator::class);
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
     * @param AppDirectoryList $directoryList
     * @param ConfigFilePool $configFilePool
     * @param array $arguments
     * @return DeploymentConfig
     */
    protected function createDeploymentConfig(
        AppDirectoryList $directoryList,
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
     * @param BooleanUtils $booleanUtils
     * @return InterpreterInterface
     */
    protected function createArgumentInterpreter(
        BooleanUtils $booleanUtils
    ) {
        $constInterpreter = new Constant();
        $result = new Composite(
            [
                'boolean' => new Boolean($booleanUtils),
                'string' => new BaseStringUtils($booleanUtils),
                'number' => new Number(),
                'null' => new NullType(),
                'object' => new DataObject($booleanUtils),
                'const' => $constInterpreter,
                'init_parameter' => new ArgumentInterpreter($constInterpreter),
            ],
            DomReader::TYPE_ATTRIBUTE
        );
        // Add interpreters that reference the composite
        $result->addInterpreter('array', new ArrayType($result));
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
     * @throws InitException
     */
    protected function _loadPrimaryConfig(DirectoryList $directoryList, $driverPool, $argumentMapper, $appMode)
    {
        $configData = null;
        try {
            $fileResolver = new Primary(
                new Filesystem(
                    $directoryList,
                    new ReadFactory($driverPool),
                    new WriteFactory($driverPool)
                ),
                new FileIteratorFactory(
                    new FileReadFactory($driverPool)
                )
            );
            $schemaLocator = new SchemaLocator();
            $validationState = new ValidationState($appMode);

            $reader = new DomReader(
                $fileResolver,
                $argumentMapper,
                $schemaLocator,
                $validationState
            );
            $configData = $reader->read('primary');
        } catch (\Exception $e) {
            throw new InitException(
                new Phrase($e->getMessage()),
                $e
            );
        }
        return $configData;
    }

    /**
     * Crete plugin list object
     *
     * @param ObjectManagerInterface $objectManager
     * @param RelationsInterface $relations
     * @param DefinitionFactory $definitionFactory
     * @param DiConfig $diConfig
     * @param DefinitionInterface $definitions
     * @return PluginList
     * @deprecated 101.0.0 Use ObjectManager::create() directly instead
     * @see ObjectManagerInterface::create()
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _createPluginList(
        ObjectManagerInterface $objectManager,
        RelationsInterface $relations,
        DefinitionFactory $definitionFactory,
        DiConfig $diConfig,
        DefinitionInterface $definitions
    ) {
        return $objectManager->create(
            PluginList::class,
            [
                'relations' => $relations,
                'definitions' => $definitionFactory->createPluginDefinition(),
                'omConfig' => $diConfig,
                'classDefinitions' => null
            ]
        );
    }
}
