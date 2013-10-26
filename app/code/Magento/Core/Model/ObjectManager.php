<?php
/**
 * Magento application object manager. Configures and application application
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObjectManager extends \Magento\ObjectManager\ObjectManager
{
    /**
     * @var \Magento\Core\Model\ObjectManager
     */
    protected static $_instance;

    /**
     * @var \Magento\ObjectManager\Relations
     */
    protected $_compiledRelations;

    /**
     * Retrieve object manager
     *
     * TODO: Temporary solution for serialization, should be removed when Serialization problem is resolved
     *
     * @deprecated
     * @return \Magento\ObjectManager
     * @throws \RuntimeException
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof \Magento\ObjectManager) {
            throw new \RuntimeException('ObjectManager isn\'t initialized');
        }
        return self::$_instance;
    }

    /**
     * Set object manager instance
     *
     * @param \Magento\ObjectManager $objectManager
     * @throws \LogicException
     */
    public static function setInstance(\Magento\ObjectManager $objectManager)
    {
        self::$_instance = $objectManager;
    }

    /**
     * @param \Magento\Core\Model\Config\Primary $primaryConfig
     * @param \Magento\ObjectManager\Config $config
     * @param array $sharedInstances
     * @param \Magento\Core\Model\ObjectManager\ConfigLoader\Primary $primaryLoader
     * @throws \Magento\BootstrapException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(
        \Magento\Core\Model\Config\Primary $primaryConfig,
        \Magento\ObjectManager\Config $config = null,
        $sharedInstances = array(),
        \Magento\Core\Model\ObjectManager\ConfigLoader\Primary $primaryLoader = null
    ) {
        $definitionFactory = new \Magento\Core\Model\ObjectManager\DefinitionFactory($primaryConfig);
        $definitions = $definitionFactory->createClassDefinition($primaryConfig);
        $relations = $definitionFactory->createRelations();
        $config = $config ?: new \Magento\ObjectManager\Config\Config(
            $relations,
            $definitions
        );

        $localConfig = new \Magento\Core\Model\Config\Local(new \Magento\Core\Model\Config\Loader\Local(
            $primaryConfig->getDirectories()->getDir(\Magento\App\Dir::CONFIG),
            $primaryConfig->getParam(\Magento\Core\Model\App::PARAM_CUSTOM_LOCAL_CONFIG),
            $primaryConfig->getParam(\Magento\Core\Model\App::PARAM_CUSTOM_LOCAL_FILE)
        ));
        $appMode = $primaryConfig->getParam(\Magento\Core\Model\App::PARAM_MODE, \Magento\App\State::MODE_DEFAULT);
        $factory = new \Magento\ObjectManager\Factory\Factory($config, $this, $definitions, array_replace(
            $localConfig->getParams(),
            $primaryConfig->getParams()
        ));

        $sharedInstances['Magento\Core\Model\Config\Local'] = $localConfig;
        $sharedInstances['Magento\Core\Model\Config\Primary'] = $primaryConfig;
        $sharedInstances['Magento\App\Dir'] = $primaryConfig->getDirectories();
        $sharedInstances['Magento\Core\Model\ObjectManager'] = $this;

        parent::__construct($factory, $config, $sharedInstances);
        $primaryConfig->configure($this);
        self::setInstance($this);

        \Magento\Profiler::start('global_primary');
        $primaryLoader = $primaryLoader ?: new \Magento\Core\Model\ObjectManager\ConfigLoader\Primary(
            $primaryConfig->getDirectories(),
            $appMode
        );
        try {
            $configData = $primaryLoader->load();
        } catch (\Exception $e) {
            throw new \Magento\BootstrapException($e->getMessage());
        }

        if ($configData) {
            $this->configure($configData);
        }

        if ($definitions instanceof \Magento\ObjectManager\Definition\Compiled) {
            $interceptorGenerator = null;
        } else {
            $autoloader = new \Magento\Autoload\IncludePath();
            $interceptorGenerator = new \Magento\Interception\CodeGenerator\CodeGenerator(new \Magento\Code\Generator(
                null,
                $autoloader,
                new \Magento\Code\Generator\Io(
                    new \Magento\Io\File(),
                    $autoloader,
                    $primaryConfig->getDirectories()->getDir(\Magento\App\Dir::GENERATION)
                )
            ));
        }

        \Magento\Profiler::stop('global_primary');
        $verification = $this->get('Magento\App\Dir\Verification');
        $verification->createAndVerifyDirectories();

        $this->_config->setCache($this->get('Magento\Core\Model\ObjectManager\ConfigCache'));
        $this->configure($this->get('Magento\Core\Model\ObjectManager\ConfigLoader')->load('global'));

        $interceptionConfig = $this->create('Magento\Interception\Config\Config', array(
            'relations' => $definitionFactory->createRelations(),
            'omConfig' => $this->_config,
            'codeGenerator' => $interceptorGenerator,
            'classDefinitions' => $definitions instanceof \Magento\ObjectManager\Definition\Compiled
                ? $definitions
                : null,
            'cacheId' => 'interception',
        ));

        $pluginList = $this->create('Magento\Interception\PluginList\PluginList', array(
            'relations' => $definitionFactory->createRelations(),
            'definitions' => $definitionFactory->createPluginDefinition(),
            'omConfig' => $this->_config,
            'classDefinitions' => $definitions instanceof \Magento\ObjectManager\Definition\Compiled
                ? $definitions
                : null,
            'scopePriorityScheme' => array('global'),
            'cacheId' => 'pluginlist',
        ));
        $this->_sharedInstances['Magento\Interception\PluginList\PluginList'] = $pluginList;
        $this->_factory = $this->create('Magento\Interception\FactoryDecorator', array(
            'factory' => $this->_factory,
            'config' => $interceptionConfig,
            'pluginList' => $pluginList
        ));
        $this->get('Magento\Core\Model\Resource')->setConfig($this->get('Magento\Core\Model\Config\Resource'));

        self::setInstance($this);
    }
}
