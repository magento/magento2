<?php
/**
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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Layout_TranslatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout_Translator
     */
    protected $_object;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var SimpleXMLElement
     */
    protected $_xmlDocument;

    protected function setUp()
    {
        $this->_helperFactoryMock = $this->getMock('Mage_Core_Model_Factory_Helper', array(), array(), '', false);
        $this->_helperMock = $this->getMock('Mage_Core_Helper_Data', array(), array(), '', false);
        $this->_helperMock->expects($this->any())->method('__')->with('test')->will($this->returnValue('translated'));

        $string = <<<XML
<?xml version='1.0'?>
<layout>
    <arguments>
        <node_self_translated translate="true">test</node_self_translated>
        <node_no_self_translated>test</node_no_self_translated>
    </arguments>
    <arguments_parent translate="node node_other" module="Some_Module">
        <node>test</node>
        <node_other module="Other_Module"> test </node_other>
        <node_no_translated>no translated</node_no_translated>
    </arguments_parent>
    <action_one method="someMethod" />
    <action_two method="someMethod" module='Some_Module' translate='one two' />
    <action_three method="someMethod" module='Some_Module' translate='one two.value' />
    <action_four method="someMethod" translate='one two' />
</layout>
XML;

        $this->_xmlDocument = simplexml_load_string($string, 'Varien_Simplexml_Element');

        $params = array(
            'helperRegistry' => $this->_helperFactoryMock
        );
        $this->_object = new Mage_Core_Model_Layout_Translator($params);
    }

    /**
     * @covers Mage_Core_Model_Layout_Translator::translateActionParameters
     */
    public function testTranslateActionParametersWithNonTranslatedArgument()
    {
        $args = array('one' => 'test');
        $this->_helperFactoryMock->expects($this->never())->method('get');

        $this->_object->translateActionParameters($this->_xmlDocument->action_one, $args);
        $this->assertEquals('test', $args['one']);
    }

    /**
     * @covers Mage_Core_Model_Layout_Translator::translateActionParameters
     */
    public function testTranslateActionParametersWithTranslatedArgument()
    {
        $args = array('one' => 'test', 'two' => 'test', 'three' => 'test');
        $expected = array('one' => 'translated', 'two' => 'translated', 'three' => 'test');
        $this->_helperFactoryMock->expects($this->exactly(2))
            ->method('get')
            ->with('Some_Module')
            ->will($this->returnValue($this->_helperMock));

        $this->_object->translateActionParameters($this->_xmlDocument->action_two, $args);
        $this->assertEquals($expected, $args);
    }

    /**
     * @covers Mage_Core_Model_Layout_Translator::translateActionParameters
     */
    public function testTranslateActionParametersWithHierarchyTranslatedArgumentAndNonStringParam()
    {
        $args = array('one' => array('some', 'data'), 'two' => array('value' => 'test'), 'three' => 'test');
        $expected = array('one' =>  array('some', 'data'), 'two' => array('value' => 'translated'), 'three' => 'test');

        $this->_helperFactoryMock->expects($this->exactly(1))
            ->method('get')
            ->with('Some_Module')
            ->will($this->returnValue($this->_helperMock));

        $this->_object->translateActionParameters($this->_xmlDocument->action_three, $args);
        $this->assertEquals($expected, $args);
    }

    /**
     * @covers Mage_Core_Model_Layout_Translator::translateActionParameters
     */
    public function testTranslateActionParametersWithoutModule()
    {
        $args = array('two' => 'test', 'three' => 'test');
        $expected = array('two' => 'translated', 'three' => 'test');
        $this->_helperFactoryMock->expects($this->once())
            ->method('get')
            ->with('Mage_Core')
            ->will($this->returnValue($this->_helperMock));

        $this->_object->translateActionParameters($this->_xmlDocument->action_four, $args);
        $this->assertEquals($expected, $args);
    }

    /**
     * @covers Mage_Core_Model_Layout_Translator::translateArgument
     */
    public function testTranslateArgumentWithDefaultModuleAndSelfTranslatedMode()
    {
        $this->_helperFactoryMock->expects($this->once())
            ->method('get')
            ->with('Some_Module')
            ->will($this->returnValue($this->_helperMock));

        $actual = $this->_object->translateArgument(
            $this->_xmlDocument->arguments->node_self_translated,
            'Some_Module'
        );
        $this->assertEquals('translated', $actual);
    }

    /**
     * @covers Mage_Core_Model_Layout_Translator::translateArgument
     */
    public function testTranslateArgumentWithoutModuleAndSelfTranslatedMode()
    {
        $this->_helperFactoryMock->expects($this->once())
            ->method('get')
            ->with('Mage_Core')
            ->will($this->returnValue($this->_helperMock));

        $actual = $this->_object->translateArgument($this->_xmlDocument->arguments->node_self_translated);
        $this->assertEquals('translated', $actual);
    }

    /**
     * @covers Mage_Core_Model_Layout_Translator::translateArgument
     */
    public function testTranslateArgumentWithoutModuleAndNoSelfTranslatedMode()
    {
        $this->_helperFactoryMock->expects($this->never())->method('get');
        $actual = $this->_object->translateArgument($this->_xmlDocument->arguments->node_no_self_translated);
        $this->assertEquals('test', $actual);
    }

    /**
     * @covers Mage_Core_Model_Layout_Translator::translateArgument
     */
    public function testTranslateArgumentViaParentNodeWithParentModule()
    {
        $this->_helperFactoryMock->expects($this->once())
            ->method('get')
            ->with('Some_Module')
            ->will($this->returnValue($this->_helperMock));

        $actual = $this->_object->translateArgument($this->_xmlDocument->arguments_parent->node, 'Some_Module');
        $this->assertEquals('translated', $actual);
    }

    /**
     * @covers Mage_Core_Model_Layout_Translator::translateArgument
     */
    public function testTranslateArgumentViaParentNodeWithOwnModule()
    {
        $this->_helperFactoryMock->expects($this->once())
            ->method('get')
            ->with('Other_Module')
            ->will($this->returnValue($this->_helperMock));

        $actual = $this->_object->translateArgument($this->_xmlDocument->arguments_parent->node_other, 'Some_Module');
        $this->assertEquals('translated', $actual);
    }

    /**
     * @covers Mage_Core_Model_Layout_Translator::translateArgument
     */
    public function testTranslateArgumentViaParentWithNodeThatIsNotInTranslateList()
    {
        $this->_helperFactoryMock->expects($this->never())->method('get');
        $actual = $this->_object->translateArgument($this->_xmlDocument->arguments_parent->node_no_translated);
        $this->assertEquals('no translated', $actual);
    }
}
