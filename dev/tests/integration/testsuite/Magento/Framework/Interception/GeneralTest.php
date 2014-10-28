<?php
/**
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
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    public function setUp()
    {
        $classReader = new \Magento\Framework\Code\Reader\ClassReader();
        $relations = new \Magento\Framework\ObjectManager\Relations\Runtime($classReader);
        $definitions = new \Magento\Framework\ObjectManager\Definition\Runtime($classReader);
        $config = new \Magento\Framework\Interception\ObjectManager\Config($relations, $definitions);
        $factory = new \Magento\Framework\ObjectManager\Factory\Factory(
            $config,
            null,
            $definitions
        );

        $this->_configReader = $this->getMock('Magento\Framework\Config\ReaderInterface');
        $this->_configReader->expects(
            $this->any()
        )->method(
            'read'
        )->will(
            $this->returnValue(
                array(
                    'Magento\Framework\Interception\Fixture\InterceptedInterface' => array(
                        'plugins' => array(
                            'first' => array(
                                'instance' => 'Magento\Framework\Interception\Fixture\Intercepted\InterfacePlugin',
                                'sortOrder' => 10
                            )
                        )
                    ),
                    'Magento\Framework\Interception\Fixture\Intercepted' => array(
                        'plugins' => array(
                            'second' => array(
                                'instance' => 'Magento\Framework\Interception\Fixture\Intercepted\Plugin',
                                'sortOrder' => 20
                            )
                        )
                    )
                )
            )
        );

        $areaList = $this->getMock('Magento\Framework\App\AreaList', array(), array(), '', false);
        $areaList->expects($this->any())->method('getCodes')->will($this->returnValue(array()));
        $configScope = new \Magento\Framework\Config\Scope($areaList, 'global');
        $cache = $this->getMock('Magento\Framework\Config\CacheInterface');
        $cache->expects($this->any())->method('load')->will($this->returnValue(false));
        $definitions = new \Magento\Framework\ObjectManager\Definition\Runtime();
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
            array(
                'Magento\Framework\Config\CacheInterface' => $cache,
                'Magento\Framework\Config\ScopeInterface' => $configScope,
                'Magento\Framework\Config\ReaderInterface' => $this->_configReader,
                'Magento\Framework\ObjectManager\Relations' => $relations,
                'Magento\Framework\ObjectManager\Config' => $config,
                'Magento\Framework\ObjectManager\Definition' => $definitions,
                'Magento\Framework\Interception\Definition' => $interceptionDefinitions
            )
        );
        $factory->setObjectManager($this->_objectManager);
        $config->setInterceptionConfig($interceptionConfig);
        $config->extend(
            array(
                'preferences' => array(
                    'Magento\Framework\Interception\PluginList' =>
                        'Magento\Framework\Interception\PluginList\PluginList',
                    'Magento\Framework\Interception\Chain' => 'Magento\Framework\Interception\Chain\Chain'
                )
            )
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
