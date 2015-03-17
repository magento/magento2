<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception;

use Magento\Framework\App;
use Magento\Framework\Config\Scope;
use Magento\Framework\Interception\ObjectManager\Config\Developer;
use Magento\Framework\ObjectManager\Definition;
use Magento\Framework\ObjectManager\Factory\Dynamic\Developer as DeveloperFactory;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManager\Relations;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class GeneralTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractPlugin extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configReader;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ObjectManagerInterface
     */
    private $applicationObjectManager;

    public function setUp()
    {
        if (!$this->_objectManager) {
            return;
        }

        $this->applicationObjectManager = App\ObjectManager::getInstance();
        App\ObjectManager::setInstance($this->_objectManager);
    }

    public function tearDown()
    {
        App\ObjectManager::setInstance($this->applicationObjectManager);
    }

    public function setUpInterceptionConfig($pluginConfig)
    {
        $config = new Developer();
        $factory = new DeveloperFactory($config, null);

        $this->_configReader = $this->getMock('Magento\Framework\Config\ReaderInterface');
        $this->_configReader->expects(
            $this->any()
        )->method(
            'read'
        )->will(
            $this->returnValue($pluginConfig)
        );

        $areaList = $this->getMock('Magento\Framework\App\AreaList', [], [], '', false);
        $areaList->expects($this->any())->method('getCodes')->will($this->returnValue([]));
        $configScope = new Scope($areaList, 'global');
        $cache = $this->getMock('Magento\Framework\Config\CacheInterface');
        $cache->expects($this->any())->method('load')->will($this->returnValue(false));
        $definitions = new Definition\Runtime();
        $relations = new Relations\Runtime();
        $interceptionConfig = new Config\Config(
            $this->_configReader,
            $configScope,
            $cache,
            $relations,
            $config,
            $definitions
        );
        $interceptionDefinitions = new Definition\Runtime();
        $sharedInstances = [
            'Magento\Framework\Config\CacheInterface'                      => $cache,
            'Magento\Framework\Config\ScopeInterface'                      => $configScope,
            'Magento\Framework\Config\ReaderInterface'                     => $this->_configReader,
            'Magento\Framework\ObjectManager\RelationsInterface'           => $relations,
            'Magento\Framework\ObjectManager\ConfigInterface'              => $config,
            'Magento\Framework\Interception\ObjectManager\ConfigInterface' => $config,
            'Magento\Framework\ObjectManager\DefinitionInterface'          => $definitions,
            'Magento\Framework\Interception\DefinitionInterface'           => $interceptionDefinitions
        ];
        $this->_objectManager = new ObjectManager(
            $factory,
            $config,
            $sharedInstances
        );
        $factory->setObjectManager($this->_objectManager);

        $config->setInterceptionConfig($interceptionConfig);
        $config->extend(
            [
                'preferences' => [
                    'Magento\Framework\Interception\PluginListInterface' =>
                        'Magento\Framework\Interception\PluginList\PluginList',
                    'Magento\Framework\Interception\ChainInterface'      =>
                        'Magento\Framework\Interception\Chain\Chain',
                ],
            ]
        );
    }
}
