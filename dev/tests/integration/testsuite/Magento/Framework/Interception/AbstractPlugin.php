<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Interception;

use Magento\Framework\ObjectManager\Config\Config as ObjectManagerConfig;

/**
 * Class GeneralTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractPlugin extends \PHPUnit_Framework_TestCase
{
    public function setUpInterceptionConfig($pluginConfig)
    {
        $config = new \Magento\Framework\Interception\ObjectManager\Config\Developer();
        $factory = new \Magento\Framework\ObjectManager\Factory\Dynamic\Developer($config, null);

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
        $configScope = new \Magento\Framework\Config\Scope($areaList, 'global');
        $cache = $this->getMock('Magento\Framework\Config\CacheInterface');
        $cache->expects($this->any())->method('load')->will($this->returnValue(false));
        $definitions = new \Magento\Framework\ObjectManager\Definition\Runtime();
        $relations = new \Magento\Framework\ObjectManager\Relations\Runtime();
        $interceptionConfig = new Config\Config(
            $this->_configReader,
            $configScope,
            $cache,
            $relations,
            $config,
            $definitions
        );
        $interceptionDefinitions = new Definition\Runtime();
        $this->_objectManager = new \Magento\Framework\ObjectManager\ObjectManager(
            $factory,
            $config,
            [
                'Magento\Framework\Config\CacheInterface'                      => $cache,
                'Magento\Framework\Config\ScopeInterface'                      => $configScope,
                'Magento\Framework\Config\ReaderInterface'                     => $this->_configReader,
                'Magento\Framework\ObjectManager\RelationsInterface'           => $relations,
                'Magento\Framework\ObjectManager\ConfigInterface'              => $config,
                'Magento\Framework\Interception\ObjectManager\ConfigInterface' => $config,
                'Magento\Framework\ObjectManager\DefinitionInterface'          => $definitions,
                'Magento\Framework\Interception\DefinitionInterface'           => $interceptionDefinitions
            ]
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
