<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class GeneralTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractPlugin extends \PHPUnit\Framework\TestCase
{
    /**
     * Config reader
     *
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_configReader;

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Applicartion Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $applicationObjectManager;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        if (!$this->_objectManager) {
            return;
        }

        $this->applicationObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        \Magento\Framework\App\ObjectManager::setInstance($this->_objectManager);
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
    {
        \Magento\Framework\App\ObjectManager::setInstance($this->applicationObjectManager);
    }

    /**
     * Set up Interception Config
     *
     * @param array $pluginConfig
     */
    public function setUpInterceptionConfig($pluginConfig)
    {
        $config = new \Magento\Framework\Interception\ObjectManager\Config\Developer();
        $factory = new \Magento\Framework\ObjectManager\Factory\Dynamic\Developer($config, null);

        $this->_configReader = $this->createMock(\Magento\Framework\Config\ReaderInterface::class);
        $this->_configReader->expects(
            $this->any()
        )->method(
            'read'
        )->willReturn(
            $pluginConfig
        );

        $areaList = $this->createMock(\Magento\Framework\App\AreaList::class);
        $areaList->expects($this->any())->method('getCodes')->willReturn([]);
        $configScope = new \Magento\Framework\Config\Scope($areaList, 'global');
        $cache = $this->createMock(\Magento\Framework\Config\CacheInterface::class);
        $cacheManager = $this->createMock(\Magento\Framework\Interception\Config\CacheManager::class);
        $cacheManager->method('load')->willReturn(null);
        $definitions = new \Magento\Framework\ObjectManager\Definition\Runtime();
        $relations = new \Magento\Framework\ObjectManager\Relations\Runtime();
        $configLoader = $this->createMock(ConfigLoaderInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $directoryList = $this->createMock(DirectoryList::class);
        $configWriter = $this->createMock(PluginListGenerator::class);
        $interceptionConfig = new Config\Config(
            $this->_configReader,
            $configScope,
            $cache,
            $relations,
            $config,
            $definitions,
            'interception',
            null,
            $cacheManager
        );
        $interceptionDefinitions = new Definition\Runtime();
        $json = new \Magento\Framework\Serialize\Serializer\Json();
        $sharedInstances = [
            \Magento\Framework\Config\CacheInterface::class                      => $cache,
            \Magento\Framework\Config\ScopeInterface::class                      => $configScope,
            \Magento\Framework\Config\ReaderInterface::class                     => $this->_configReader,
            \Magento\Framework\ObjectManager\RelationsInterface::class           => $relations,
            \Magento\Framework\ObjectManager\ConfigInterface::class              => $config,
            \Magento\Framework\Interception\ObjectManager\ConfigInterface::class => $config,
            \Magento\Framework\ObjectManager\DefinitionInterface::class          => $definitions,
            \Magento\Framework\Interception\DefinitionInterface::class           => $interceptionDefinitions,
            \Magento\Framework\Serialize\SerializerInterface::class              => $json,
            \Magento\Framework\Interception\ConfigLoaderInterface::class         => $configLoader,
            \Psr\Log\LoggerInterface::class                                      => $logger,
            \Magento\Framework\App\Filesystem\DirectoryList::class               => $directoryList,
            \Magento\Framework\App\ObjectManager\ConfigWriterInterface::class    => $configWriter
        ];
        $this->_objectManager = new \Magento\Framework\ObjectManager\ObjectManager(
            $factory,
            $config,
            $sharedInstances
        );
        $factory->setObjectManager($this->_objectManager);

        $config->setInterceptionConfig($interceptionConfig);
        $config->extend(
            [
                'preferences' => [
                    \Magento\Framework\Interception\PluginListInterface::class =>
                        \Magento\Framework\Interception\PluginList\PluginList::class,
                    \Magento\Framework\Interception\ConfigWriterInterface::class =>
                        \Magento\Framework\Interception\PluginListGenerator::class
                ],
            ]
        );
    }
}
