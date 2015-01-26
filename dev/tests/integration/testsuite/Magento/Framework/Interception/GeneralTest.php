<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception;

/**
 * Class GeneralTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneralTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configReader;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function setUp()
    {
        $config = new \Magento\Framework\Interception\ObjectManager\Config\Developer();
        $factory = new \Magento\Framework\ObjectManager\Factory\Dynamic\Developer($config, null);

        $this->_configReader = $this->getMock('Magento\Framework\Config\ReaderInterface');
        $this->_configReader->expects(
            $this->any()
        )->method(
            'read'
        )->will(
            $this->returnValue(
                [
                    'Magento\Framework\Interception\Fixture\InterceptedInterface' => [
                        'plugins' => [
                            'first' => [
                                'instance' => 'Magento\Framework\Interception\Fixture\Intercepted\InterfacePlugin',
                                'sortOrder' => 10,
                            ],
                        ],
                    ],
                    'Magento\Framework\Interception\Fixture\Intercepted' => [
                        'plugins' => [
                            'second' => [
                                'instance' => 'Magento\Framework\Interception\Fixture\Intercepted\Plugin',
                                'sortOrder' => 20,
                            ],
                        ],
                    ],
                ]
            )
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
                'Magento\Framework\Config\CacheInterface' => $cache,
                'Magento\Framework\Config\ScopeInterface' => $configScope,
                'Magento\Framework\Config\ReaderInterface' => $this->_configReader,
                'Magento\Framework\ObjectManager\RelationsInterface' => $relations,
                'Magento\Framework\ObjectManager\ConfigInterface' => $config,
                'Magento\Framework\Interception\ObjectManager\ConfigInterface' => $config,
                'Magento\Framework\ObjectManager\DefinitionInterface' => $definitions,
                'Magento\Framework\Interception\DefinitionInterface' => $interceptionDefinitions
            ]
        );
        $factory->setObjectManager($this->_objectManager);
        $config->setInterceptionConfig($interceptionConfig);
        $config->extend(
            [
                'preferences' => [
                    'Magento\Framework\Interception\PluginListInterface' =>
                        'Magento\Framework\Interception\PluginList\PluginList',
                    'Magento\Framework\Interception\ChainInterface' => 'Magento\Framework\Interception\Chain\Chain',
                ],
            ]
        );
    }

    public function testMethodCanBePluginized()
    {
        $subject = $this->_objectManager->create('Magento\Framework\Interception\Fixture\Intercepted');
        $this->assertEquals('<P:D>1: <D>test</D></P:D>', $subject->D('test'));
    }

    public function testPluginCanCallOnlyNextMethodOnNext()
    {
        $subject = $this->_objectManager->create('Magento\Framework\Interception\Fixture\Intercepted');
        $this->assertEquals(
            '<IP:aG><P:aG><G><P:G><P:bG><IP:G><IP:bG>test</IP:bG></IP:G></P:bG></P:G></G></P:aG></IP:aG>',
            $subject->G('test')
        );
    }

    public function testBeforeAndAfterPluginsAreExecuted()
    {
        $subject = $this->_objectManager->create('Magento\Framework\Interception\Fixture\Intercepted');
        $this->assertEquals(
            '<IP:F><P:D>1: <D>prefix_<F><IP:C><P:C><C>test</C></P:C>' . '</IP:C></F></D></P:D></IP:F>',
            $subject->A('prefix_')->F('test')
        );
    }

    public function testPluginCallsOtherMethodsOnSubject()
    {
        $subject = $this->_objectManager->create('Magento\Framework\Interception\Fixture\Intercepted');
        $this->assertEquals(
            '<P:K><IP:F><P:D>1: <D>prefix_<F><IP:C><P:C><C><IP:C><P:C><C>test' .
            '</C></P:C></IP:C></C></P:C></IP:C></F></D></P:D></IP:F></P:K>',
            $subject->A('prefix_')->K('test')
        );
    }

    public function testInterfacePluginsAreInherited()
    {
        $subject = $this->_objectManager->create('Magento\Framework\Interception\Fixture\Intercepted');
        $this->assertEquals('<IP:C><P:C><C>test</C></P:C></IP:C>', $subject->C('test'));
    }

    public function testInternalMethodCallsAreIntercepted()
    {
        $subject = $this->_objectManager->create('Magento\Framework\Interception\Fixture\Intercepted');
        $this->assertEquals('<B>12<IP:C><P:C><C>1</C></P:C></IP:C></B>', $subject->B('1', '2'));
    }

    public function testChainedMethodsAreIntercepted()
    {
        $subject = $this->_objectManager->create('Magento\Framework\Interception\Fixture\Intercepted');
        $this->assertEquals('<P:D>1: <D>prefix_test</D></P:D>', $subject->A('prefix_')->D('test'));
    }

    public function testFinalMethodWorks()
    {
        $subject = $this->_objectManager->create('Magento\Framework\Interception\Fixture\Intercepted');
        $this->assertEquals('<P:D>1: <D>prefix_test</D></P:D>', $subject->A('prefix_')->D('test'));
        $this->assertEquals('<E>prefix_final</E>', $subject->E('final'));
        $this->assertEquals('<P:D>2: <D>prefix_test</D></P:D>', $subject->D('test'));
    }

    public function testObjectKeepsStateBetweenInvocations()
    {
        $subject = $this->_objectManager->create('Magento\Framework\Interception\Fixture\Intercepted');
        $this->assertEquals('<P:D>1: <D>test</D></P:D>', $subject->D('test'));
        $this->assertEquals('<P:D>2: <D>test</D></P:D>', $subject->D('test'));
    }
}
