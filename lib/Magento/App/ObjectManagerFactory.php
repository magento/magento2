<?php
/**
 * Initialize application object manager.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\App;

use Magento\App\Arguments,
    Magento\Profiler,
    Magento\App\Filesystem;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * Class ObjectManagerFactory
 * @package Magento\App
 */
class ObjectManagerFactory
{
    /**
     * Locator class name
     *
     * @var string
     */
    protected $_locatorClassName = '\Magento\ObjectManager\ObjectManager';

    /**
     * Config class name
     *
     * @var string
     */
    protected $_configClassName = '\Magento\ObjectManager\Config\Config';

    /**
     * Create object manager
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param string $rootDir
     * @param array $arguments
     * @return \Magento\ObjectManager\ObjectManager
     * @throws \Magento\BootstrapException
     */
    public function create($rootDir, array $arguments)
    {
        $directories = isset($arguments[\Magento\App\Filesystem::PARAM_APP_DIRS])
            ? $arguments[\Magento\App\Filesystem::PARAM_APP_DIRS]
            : array();
        $directoryList = new \Magento\App\Filesystem\DirectoryList($rootDir, $directories);

        \Magento\Autoload\IncludePath::addIncludePath(
            array($directoryList->getDir(\Magento\App\Filesystem::GENERATION_DIR))
        );

        $appArguments = $this->createAppArguments($directoryList, $arguments);

        $definitionFactory = new \Magento\ObjectManager\DefinitionFactory(
            new \Magento\Filesystem\Driver\File(),
            $directoryList->getDir(\Magento\App\Filesystem::DI_DIR),
            $directoryList->getDir(\Magento\App\Filesystem::GENERATION_DIR),
            $appArguments->get('definition.format', 'serialized')
        );

        $definitions = $definitionFactory->createClassDefinition($appArguments->get('definitions'));
        $relations = $definitionFactory->createRelations();
        $configClass = $this->_configClassName;
        /** @var \Magento\ObjectManager\Config\Config $diConfig */
        $diConfig = new $configClass($relations, $definitions);
        $appMode = $appArguments->get(State::PARAM_MODE, State::MODE_DEFAULT);

        $configData = $this->_loadPrimaryConfig($directoryList, $appMode);

        if ($configData) {
            $diConfig->extend($configData);
        }

        $booleanUtils = new \Magento\Stdlib\BooleanUtils();
        $argFactory = new \Magento\ObjectManager\Config\Argument\ObjectFactory($diConfig);
        $argInterpreter = $this->createArgumentInterpreter($booleanUtils, $argFactory, $appArguments);
        $factory = new \Magento\ObjectManager\Factory\Factory($diConfig, $argInterpreter, $argFactory, $definitions);

        $className = $this->_locatorClassName;
        /** @var \Magento\ObjectManager $objectManager */
        $objectManager = new $className($factory, $diConfig, array(
            'Magento\App\Arguments' => $appArguments,
            'Magento\App\Filesystem\DirectoryList' => $directoryList,
            'Magento\Filesystem\DirectoryList' => $directoryList,
            'Magento\Stdlib\BooleanUtils' => $booleanUtils,
        ));

        $argFactory->setObjectManager($objectManager);
        \Magento\App\ObjectManager::setInstance($objectManager);

        /** @var \Magento\App\Filesystem\DirectoryList\Verification $verification */
        $verification = $objectManager->get('Magento\App\Filesystem\DirectoryList\Verification');
        $verification->createAndVerifyDirectories();

        $diConfig->setCache($objectManager->get('Magento\App\ObjectManager\ConfigCache'));
        $objectManager->configure(
            $objectManager->get('Magento\App\ObjectManager\ConfigLoader')->load('global')
        );
        $objectManager->get('Magento\Config\ScopeInterface')->setCurrentScope('global');
        $objectManager->get('Magento\App\Resource')->setCache($objectManager->get('Magento\App\CacheInterface'));

        $relations = $definitionFactory->createRelations();

        $interceptionConfig = $objectManager->create('Magento\Interception\Config\Config', array(
            'relations' => $relations,
            'omConfig' => $diConfig,
            'classDefinitions' => $definitions instanceof \Magento\ObjectManager\Definition\Compiled
                ? $definitions
                : null,
        ));

        $pluginList = $this->_createPluginList($objectManager, $relations, $definitionFactory, $diConfig, $definitions);

        $factory = $objectManager->create('Magento\Interception\FactoryDecorator', array(
            'factory' => $factory,
            'config' => $interceptionConfig,
            'pluginList' => $pluginList
        ));
        $objectManager->setFactory($factory);

        $this->configureDirectories($objectManager);

        return $objectManager;
    }

    /**
     * Create instance of application arguments
     *
     * @param Filesystem\DirectoryList $directoryList
     * @param array $arguments
     * @return Arguments
     */
    protected function createAppArguments(\Magento\App\Filesystem\DirectoryList $directoryList, array $arguments)
    {
        return new \Magento\App\Arguments(
            $arguments,
            new \Magento\App\Arguments\Loader(
                $directoryList,
                isset($arguments[\Magento\App\Arguments\Loader::PARAM_CUSTOM_FILE])
                    ? $arguments[\Magento\App\Arguments\Loader::PARAM_CUSTOM_FILE]
                    : null
            )
        );
    }

    /**
     * Return newly created instance on an argument interpreter, suitable for processing DI arguments
     *
     * @param \Magento\Stdlib\BooleanUtils $booleanUtils
     * @param \Magento\ObjectManager\Config\Argument\ObjectFactory $objFactory
     * @param \Magento\App\Arguments $appArguments
     * @return \Magento\Data\Argument\InterpreterInterface
     */
    protected function createArgumentInterpreter(
        \Magento\Stdlib\BooleanUtils $booleanUtils,
        \Magento\ObjectManager\Config\Argument\ObjectFactory $objFactory,
        \Magento\App\Arguments $appArguments
    ) {
        $constInterpreter = new \Magento\Data\Argument\Interpreter\Constant();
        $result = new \Magento\Data\Argument\Interpreter\Composite(
            array(
                'boolean' => new \Magento\Data\Argument\Interpreter\Boolean($booleanUtils),
                'string' => new \Magento\Data\Argument\Interpreter\String($booleanUtils),
                'number' => new \Magento\Data\Argument\Interpreter\Number(),
                'null' => new \Magento\Data\Argument\Interpreter\NullType(),
                'const' => $constInterpreter,
                'object' => new \Magento\ObjectManager\Config\Argument\Interpreter\Object($booleanUtils, $objFactory),
                'init_parameter' => new \Magento\App\Arguments\ArgumentInterpreter($appArguments, $constInterpreter),
            ),
            \Magento\ObjectManager\Config\Reader\Dom::TYPE_ATTRIBUTE
        );
        // Add interpreters that reference the composite
        $result->addInterpreter('array', new \Magento\Data\Argument\Interpreter\ArrayType($result));
        return $result;
    }

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    protected function configureDirectories(\Magento\ObjectManager $objectManager)
    {
        $directoryList = $objectManager->get('Magento\App\Filesystem\DirectoryList');
        $directoryListConfig = $objectManager->get('Magento\App\Filesystem\DirectoryList\Configuration');
        $directoryListConfig->configure($directoryList);
    }

    /**
     * Load primary config data
     *
     * @param \Magento\Filesystem\DirectoryList $directoryList
     * @param string $appMode
     * @return array
     * @throws \Magento\BootstrapException
     */
    protected function _loadPrimaryConfig($directoryList, $appMode)
    {
        $configData = null;
        $primaryLoader = new \Magento\App\ObjectManager\ConfigLoader\Primary($directoryList, $appMode);
        try {
            $configData = $primaryLoader->load();
        } catch (\Exception $e) {
            throw new \Magento\BootstrapException($e->getMessage());
        }
        return $configData;
    }

    /**
     * Crete plugin list object
     *
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\ObjectManager\Relations $relations
     * @param \Magento\ObjectManager\DefinitionFactory $definitionFactory
     * @param \Magento\ObjectManager\Config\Config $diConfig
     * @param \Magento\ObjectManager\Definition $definitions
     * @return \Magento\Interception\PluginList\PluginList
     */
    protected function _createPluginList(
        \Magento\ObjectManager $objectManager,
        \Magento\ObjectManager\Relations $relations,
        \Magento\ObjectManager\DefinitionFactory $definitionFactory,
        \Magento\ObjectManager\Config\Config $diConfig,
        \Magento\ObjectManager\Definition $definitions
    ) {
        return $objectManager->create('Magento\Interception\PluginList\PluginList', array(
            'relations' => $relations,
            'definitions' => $definitionFactory->createPluginDefinition(),
            'omConfig' => $diConfig,
            'classDefinitions' => $definitions instanceof \Magento\ObjectManager\Definition\Compiled
                    ? $definitions
                    : null,
        ));
    }
}
