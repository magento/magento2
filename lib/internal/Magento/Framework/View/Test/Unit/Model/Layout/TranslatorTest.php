<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Model\Layout;

use Magento\Framework\Phrase;

class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Model\Layout\Translator
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var \SimpleXMLElement
     */
    protected $_xmlDocument;

    protected function setUp()
    {
        $string = <<<XML
<?xml version='1.0'?>
<layout>
    <arguments>
        <node_self_translated translate="true">test</node_self_translated>
        <node_no_self_translated>test</node_no_self_translated>
    </arguments>
    <arguments_parent translate="node node_other">
        <node>test</node>
        <node_other> test </node_other>
        <node_no_translated>no translated</node_no_translated>
    </arguments_parent>
    <action_one method="someMethod" />
    <action_two method="someMethod" translate='one two' />
    <action_three method="someMethod" translate='one two.value' />
    <action_four method="someMethod" translate='one two' />
</layout>
XML;

        $this->_xmlDocument = simplexml_load_string($string, \Magento\Framework\Simplexml\Element::class);

        $this->_object = new \Magento\Framework\View\Model\Layout\Translator();
    }

    /**
     * @covers \Magento\Framework\View\Model\Layout\Translator::translateActionParameters
     */
    public function testTranslateActionParametersWithNonTranslatedArgument()
    {
        $args = ['one' => 'test'];

        $this->_object->translateActionParameters($this->_xmlDocument->action_one, $args);
        $this->assertEquals('test', $args['one']);
    }

    /**
     * @covers \Magento\Framework\View\Model\Layout\Translator::translateActionParameters
     */
    public function testTranslateActionParametersWithTranslatedArgument()
    {
        $args = ['one' => 'test', 'two' => 'test', 'three' => 'test'];
        $expected = ['one' => new Phrase('test'), 'two' => new Phrase('test'), 'three' => 'test'];

        $this->_object->translateActionParameters($this->_xmlDocument->action_two, $args);
        $this->assertEquals($expected, $args);
    }

    /**
     * @covers \Magento\Framework\View\Model\Layout\Translator::translateActionParameters
     */
    public function testTranslateActionParametersWithHierarchyTranslatedArgumentAndNonStringParam()
    {
        $args = ['one' => ['some', 'data'], 'two' => ['value' => 'test'], 'three' => 'test'];
        $expected = ['one' => ['some', 'data'], 'two' => ['value' => new Phrase('test')], 'three' => 'test'];

        $this->_object->translateActionParameters($this->_xmlDocument->action_three, $args);
        $this->assertEquals($expected, $args);
    }

    /**
     * @covers \Magento\Framework\View\Model\Layout\Translator::translateActionParameters
     */
    public function testTranslateActionParametersWithoutModule()
    {
        $args = ['two' => 'test', 'three' => 'test'];
        $expected = ['two' => new Phrase('test'), 'three' => new Phrase('test')];

        $this->_object->translateActionParameters($this->_xmlDocument->action_four, $args);
        $this->assertEquals($expected, $args);
    }

    /**
     * @covers \Magento\Framework\View\Model\Layout\Translator::translateArgument
     */
    public function testTranslateArgumentWithDefaultModuleAndSelfTranslatedMode()
    {
        $actual = $this->_object->translateArgument($this->_xmlDocument->arguments->node_self_translated);
        $this->assertEquals(new Phrase('test'), $actual);
    }

    /**
     * @covers \Magento\Framework\View\Model\Layout\Translator::translateArgument
     */
    public function testTranslateArgumentWithoutModuleAndNoSelfTranslatedMode()
    {
        $actual = $this->_object->translateArgument($this->_xmlDocument->arguments->node_no_self_translated);
        $this->assertEquals('test', $actual);
    }

    /**
     * @covers \Magento\Framework\View\Model\Layout\Translator::translateArgument
     */
    public function testTranslateArgumentViaParentNodeWithParentModule()
    {
        $actual = $this->_object->translateArgument($this->_xmlDocument->arguments_parent->node);
        $this->assertEquals(new Phrase('test'), $actual);
    }

    /**
     * @covers \Magento\Framework\View\Model\Layout\Translator::translateArgument
     */
    public function testTranslateArgumentViaParentNodeWithOwnModule()
    {
        $actual = $this->_object->translateArgument($this->_xmlDocument->arguments_parent->node_other);
        $this->assertEquals(new Phrase('test'), $actual);
    }

    /**
     * @covers \Magento\Framework\View\Model\Layout\Translator::translateArgument
     */
    public function testTranslateArgumentViaParentWithNodeThatIsNotInTranslateList()
    {
        $actual = $this->_object->translateArgument($this->_xmlDocument->arguments_parent->node_no_translated);
        $this->assertEquals('no translated', $actual);
    }
}
