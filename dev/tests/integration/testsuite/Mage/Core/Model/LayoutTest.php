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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_LayoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layout;

    public static function setUpBeforeClass()
    {
        /* Point application to predefined layout fixtures */
        Mage::getConfig()->setOptions(array(
            'design_dir' => dirname(__FILE__) . '/_files/design',
        ));
        Mage::getDesign()->setDesignTheme('test/default/default');

        /* Disable loading and saving layout cache */
        Mage::app()->getCacheInstance()->banUse('layout');
    }

    protected function setUp()
    {
        $this->_layout = new Mage_Core_Model_Layout();
        $this->_layout->getUpdate()->addHandle('layout_test_handle_main');
        $this->_layout->getUpdate()->load('layout_test_handle_extra');
    }

    /**
     * @param array $inputArguments
     * @param string $expectedArea
     * @dataProvider constructorDataProvider
     */
    public function testConstructor(array $inputArguments, $expectedArea)
    {
        $layout = new Mage_Core_Model_Layout($inputArguments);
        $this->assertEquals($expectedArea, $layout->getArea());
    }

    public function constructorDataProvider()
    {
        return array(
            'default area'  => array(array(), Mage_Core_Model_Design_Package::DEFAULT_AREA),
            'frontend area' => array(array('area' => 'frontend'), 'frontend'),
            'backend area'  => array(array('area' => 'adminhtml'), 'adminhtml'),
        );
    }

    public function testConstructorStructure()
    {
        $structure = new Magento_Data_Structure;
        $structure->createElement('test.container', array());
        $layout = new Mage_Core_Model_Layout(array('structure' => $structure));
        $this->assertTrue($layout->hasElement('test.container'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorWrongStructure()
    {
        new Mage_Core_Model_Layout(array('structure' => false));
    }

    public function testGetUpdate()
    {
        $this->assertInstanceOf('Mage_Core_Model_Layout_Update', $this->_layout->getUpdate());
    }

    public function testGetSetDirectOutput()
    {
        $this->assertFalse($this->_layout->isDirectOutput());
        $this->_layout->setDirectOutput(true);
        $this->assertTrue($this->_layout->isDirectOutput());
    }

    /**
     * @covers Mage_Core_Model_Layout::getAllBlocks
     * @covers Mage_Core_Model_Layout::generateBlocks
     * @covers Mage_Core_Model_Layout::getBlock
     */
    public function testGenerateXmlAndBlocks()
    {
        $this->_layout->generateXml();
        /* Generate fixture
        file_put_contents(dirname(__FILE__) . '/_files/_layout_update.xml', $this->_model->getNode()->asNiceXml());
        */
        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/_files/_layout_update.xml', $this->_layout->getXmlString());

        $this->assertEquals(array(), $this->_layout->getAllBlocks());

        $expectedBlocks = array(
            'root',
            'head',
            'head.calendar',
            'notifications',
            'notification_baseurl',
            'cache_notifications',
            'notification_survey',
            'notification_security',
            'messages',
            'ANONYMOUS_0',
            'index_notifications',
            'index_notifications_copy'
        );
        $this->_layout->generateElements();

        $actualBlocks = $this->_layout->getAllBlocks();
        $this->assertEquals($expectedBlocks, array_keys($actualBlocks));

        /** @var $block Mage_Adminhtml_Block_Page_Head */
        $block = $this->_layout->getBlock('head');
        $this->assertEquals('Magento Admin', $block->getTitle());

        $block = $this->_layout->getBlock('head.calendar');
        $this->assertSame($this->_layout->getBlock('head'), $block->getParentBlock());

        /** @var $block Mage_Core_Block_Template */
        $block = $this->_layout->getBlock('root');
        $this->assertEquals('popup.phtml', $block->getTemplate());
    }

    public function testRenderElement()
    {
        $utility = new Mage_Core_Utility_Layout($this);
        $layout = $utility->getLayoutFromFixture(__DIR__ . '/_files/valid_layout_updates.xml');
        $layout->getUpdate()->load('a_handle');
        $layout->generateXml()->generateElements();
        $this->assertEmpty($layout->renderElement('nonexisting_element'));
        $this->assertEquals('Value: 1Value: 2', $layout->renderElement('container1'));
        $this->assertEquals('Value: 1', $layout->renderElement('block1'));
    }

    public function testGetElementProperty()
    {
        $name = 'test';
        $this->_layout->addContainer($name, 'Test', array('option1' => 1, 'option2' => 2));
        $this->assertEquals('Test', $this->_layout->getElementProperty(
            $name, Mage_Core_Model_Layout::CONTAINER_OPT_LABEL
        ));
        $this->assertEquals(Mage_Core_Model_Layout::TYPE_CONTAINER, $this->_layout->getElementProperty($name, 'type'));
        $this->assertSame(2, $this->_layout->getElementProperty($name, 'option2'));

        $this->_layout->addBlock('Mage_Core_Block_Text', 'text', $name);
        $this->assertEquals(Mage_Core_Model_Layout::TYPE_BLOCK, $this->_layout->getElementProperty('text', 'type'));
        $this->assertSame(array('text' => 'text'), $this->_layout->getElementProperty(
            $name, Magento_Data_Structure::CHILDREN
        ));
    }

    public function testIsBlock()
    {
        $this->assertFalse($this->_layout->isBlock('container'));
        $this->assertFalse($this->_layout->isBlock('block'));
        $this->_layout->addContainer('container', 'Container');
        $this->_layout->addBlock('Mage_Core_Block_Text', 'block');
        $this->assertFalse($this->_layout->isBlock('container'));
        $this->assertTrue($this->_layout->isBlock('block'));
    }

    public function testSetUnsetBlock()
    {
        $expectedBlockName = 'block_' . __METHOD__;
        $expectedBlock = new Mage_Core_Block_Text();

        $this->_layout->setBlock($expectedBlockName, $expectedBlock);
        $this->assertSame($expectedBlock, $this->_layout->getBlock($expectedBlockName));

        $this->_layout->unsetElement($expectedBlockName);
        $this->assertFalse($this->_layout->getBlock($expectedBlockName));
        $this->assertFalse($this->_layout->hasElement($expectedBlockName));
    }

    /**
     * @dataProvider createBlockDataProvider
     */
    public function testCreateBlock($blockType, $blockName, array $blockData, $expectedName)
    {
        $expectedData = $blockData + array('type' => $blockType);

        $block = $this->_layout->createBlock($blockType, $blockName, $blockData);

        $this->assertEquals($this->_layout, $block->getLayout());
        $this->assertRegExp($expectedName, $block->getNameInLayout());
        $this->assertEquals($expectedData, $block->getData());
    }

    public function createBlockDataProvider()
    {
        return array(
            'named block' => array(
                'Mage_Core_Block_Template',
                'some_block_name_full_class',
                array('type' => 'Mage_Core_Block_Template'),
                '/^some_block_name_full_class$/'
            ),
            'no name block' => array(
                'Mage_Core_Block_Text_List',
                '',
                array(
                    'type' => 'Mage_Core_Block_Text_List',
                    'key1' => 'value1'
                ),
                '/^ANONYMOUS_.+/'
            ),
        );
    }

    /**
     * @dataProvider blockNotExistsDataProvider
     * @expectedException Mage_Core_Exception
     */
    public function testCreateBlockNotExists($name)
    {
        $this->_layout->createBlock($name);
    }

    public function blockNotExistsDataProvider()
    {
        return array(
            array(''),
            array('block_not_exists'),
        );
    }

    public function testAddBlock()
    {
        $this->assertInstanceOf('Mage_Core_Block_Text', $this->_layout->addBlock('Mage_Core_Block_Text', 'block1'));
        $block2 = new Mage_Core_Block_Text;
        $block2->setNameInLayout('block2');
        $this->_layout->addBlock($block2, '', 'block1');

        $this->assertTrue($this->_layout->hasElement('block1'));
        $this->assertTrue($this->_layout->hasElement('block2'));
        $this->assertEquals('block1', $this->_layout->getParentName('block2'));
    }

    public function testAddContainer()
    {
        $this->assertFalse($this->_layout->hasElement('container'));
        $this->_layout->addContainer('container', 'Container');
        $this->assertTrue($this->_layout->hasElement('container'));
        $this->assertTrue($this->_layout->isContainer('container'));

        $this->_layout->addContainer('container1', 'Container 1', array(), 'container', 'c1');
        $this->assertEquals('container1', $this->_layout->getChildName('container', 'c1'));
    }

    public function testGetChildBlock()
    {
        $this->_layout->addContainer('parent', 'Parent');
        $block = $this->_layout->addBlock('Mage_Core_Block_Text', 'block', 'parent', 'block_alias');
        $this->_layout->addContainer('container', 'Container', array(), 'parent', 'container_alias');
        $this->assertSame($block, $this->_layout->getChildBlock('parent', 'block_alias'));
        $this->assertFalse($this->_layout->getChildBlock('parent', 'container_alias'));
    }

    /**
     * @return Mage_Core_Model_Layout
     */
    public function testSetChild()
    {
        $this->_layout->addContainer('one', 'One');
        $this->_layout->addContainer('two', 'Two');
        $this->_layout->addContainer('three', 'Three');
        $this->assertSame($this->_layout, $this->_layout->setChild('one', 'two', ''));
        $this->_layout->setChild('one', 'three', '');
        $this->assertSame(array('two', 'three'), $this->_layout->getChildNames('one'));
        return $this->_layout;
    }

    /**
     * @param Mage_Core_Model_Layout $layout
     * @depends testSetChild
     */
    public function testReorderChild(Mage_Core_Model_Layout $layout)
    {
        $layout->addContainer('four', 'Four', array(), 'one');

        // offset +1
        $layout->reorderChild('one', 'four', 1);
        $this->assertSame(array('two', 'four', 'three'), $layout->getChildNames('one'));

        // offset -2
        $layout->reorderChild('one', 'three', 2, false);
        $this->assertSame(array('two', 'three', 'four'), $layout->getChildNames('one'));

        // after sibling
        $layout->reorderChild('one', 'two', 'three');
        $this->assertSame(array('three', 'two', 'four'), $layout->getChildNames('one'));

        // after everyone
        $layout->reorderChild('one', 'three', '-');
        $this->assertSame(array('two', 'four', 'three'), $layout->getChildNames('one'));

        // before sibling
        $layout->reorderChild('one', 'four', 'two', false);
        $this->assertSame(array('four', 'two', 'three'), $layout->getChildNames('one'));

        // before everyone
        $layout->reorderChild('one', 'two', '-', false);
        $this->assertSame(array('two', 'four', 'three'), $layout->getChildNames('one'));
    }

    /**
     * @param string $handle
     * @param string $expectedResult
     * @dataProvider sortSpecialCasesDataProvider
     */
    public function testSortSpecialCases($handle, $expectedResult)
    {
        $utility = new Mage_Core_Utility_Layout($this);
        $layout = $utility->getLayoutFromFixture(__DIR__ . '/_files/sort_special_cases.xml');
        $layout->getUpdate()->load($handle);
        $layout->generateXml()->generateElements();
        $this->assertEquals($expectedResult, $layout->renderElement('root'));
    }

    public function sortSpecialCasesDataProvider()
    {
        return array(
            'Before element which is after' => array('before_after', '312'),
            'Before element which is previous' => array('before_before', '213'),
            'After element which is after' => array('after_after', '312'),
            'After element which is previous' => array('after_previous', '321'),
        );
    }

    public function testGetChildBlocks()
    {
        $this->_layout->addContainer('parent', 'Parent');
        $block1 = $this->_layout->addBlock('Mage_Core_Block_Text', 'block1', 'parent');
        $this->_layout->addContainer('container', 'Container', array(), 'parent');
        $block2 = $this->_layout->addBlock('Mage_Core_Block_Template', 'block2', 'parent');
        $this->assertSame(array('block1' => $block1, 'block2' => $block2), $this->_layout->getChildBlocks('parent'));
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testAddBlockInvalidType()
    {
        $this->_layout->addBlock('invalid_name', 'child');
    }

    public function testIsContainer()
    {
        $block = 'block';
        $container = 'container';
        $this->_layout->addBlock('Mage_Core_Block_Text', $block);
        $this->_layout->addContainer($container, 'Container');
        $this->assertFalse($this->_layout->isContainer($block));
        $this->assertTrue($this->_layout->isContainer($container));
        $this->assertFalse($this->_layout->isContainer('invalid_name'));
    }

    public function testIsManipulationAllowed()
    {
        $this->_layout->addBlock('Mage_Core_Block_Text', 'block1');
        $this->_layout->addBlock('Mage_Core_Block_Text', 'block2', 'block1');
        $this->assertFalse($this->_layout->isManipulationAllowed('block1'));
        $this->assertFalse($this->_layout->isManipulationAllowed('block2'));

        $this->_layout->addContainer('container1', 'Container 1');
        $this->_layout->addBlock('Mage_Core_Block_Text', 'block3', 'container1');
        $this->_layout->addContainer('container2', 'Container 2', array(), 'container1');
        $this->assertFalse($this->_layout->isManipulationAllowed('container1'));
        $this->assertTrue($this->_layout->isManipulationAllowed('block3'));
        $this->assertTrue($this->_layout->isManipulationAllowed('container2'));
    }

    public function testRenameElement()
    {
        $blockName = 'block';
        $expBlockName = 'block_renamed';
        $containerName = 'container';
        $expContainerName = 'container_renamed';
        $block = $this->_layout->createBlock('Mage_Core_Block_Text', $blockName);
        $this->_layout->addContainer($containerName, 'Container');

        $this->assertEquals($block, $this->_layout->getBlock($blockName));
        $this->_layout->renameElement($blockName, $expBlockName);
        $this->assertEquals($block, $this->_layout->getBlock($expBlockName));

        $this->_layout->hasElement($containerName);
        $this->_layout->renameElement($containerName, $expContainerName);
        $this->_layout->hasElement($expContainerName);
    }

    public function testGetParentName()
    {
        $this->_layout->addContainer('one', 'One');
        $this->_layout->addContainer('two', 'Two', array(), 'one');
        $this->assertFalse($this->_layout->getParentName('one'));
        $this->assertEquals('one', $this->_layout->getParentName('two'));
    }

    public function testGetElementAlias()
    {
        $this->_layout->addContainer('one', 'One');
        $this->_layout->addContainer('two', 'One', array(), 'one', '1');
        $this->assertFalse($this->_layout->getElementAlias('one'));
        $this->assertEquals('1', $this->_layout->getElementAlias('two'));
    }

    /**
     * @covers Mage_Core_Model_Layout::addOutputElement
     * @covers Mage_Core_Model_Layout::getOutput
     * @covers Mage_Core_Model_Layout::removeOutputElement
     */
    public function testGetOutput()
    {
        $blockName = 'block_' . __METHOD__;
        $expectedText = "some_text_for_$blockName";

        $block = $this->_layout->addBlock('Mage_Core_Block_Text', $blockName);
        $block->setText($expectedText);

        $this->_layout->addOutputElement($blockName);
        // add the same element twice should not produce output duplicate
        $this->_layout->addOutputElement($blockName);
        $this->assertEquals($expectedText, $this->_layout->getOutput());

        $this->_layout->removeOutputElement($blockName);
        $this->assertEmpty($this->_layout->getOutput());
    }

    public function testGetMessagesBlock()
    {
        $this->assertInstanceOf('Mage_Core_Block_Messages', $this->_layout->getMessagesBlock());
    }

    /**
     * @param string $blockType
     * @param string $expectedClassName
     * @dataProvider getBlockSingletonDataProvider
     */
    public function testGetBlockSingleton($blockType, $expectedClassName)
    {
        $block = $this->_layout->getBlockSingleton($blockType);
        $this->assertInstanceOf($expectedClassName, $block);
        $this->assertSame($block, $this->_layout->getBlockSingleton($blockType));
    }

    public function getBlockSingletonDataProvider()
    {
        return array(
            array('Mage_Core_Block_Text', 'Mage_Core_Block_Text')
        );
    }

    public function testHelper()
    {
        $helper = $this->_layout->helper('Mage_Core_Helper_Data');
        $this->assertInstanceOf('Mage_Core_Helper_Data', $helper);
        $this->assertSame($this->_layout, $helper->getLayout());
    }

    /**
     * @dataProvider findTranslationModuleNameDefaultsDataProvider
     */
    public function testFindTranslationModuleNameDefaults($node, $moduleName)
    {
        $this->markTestIncomplete('Method it self not finished as has commented out logic.');
        $this->assertEquals($moduleName, Mage_Core_Model_Layout::findTranslationModuleName($node));
    }

    public function findTranslationModuleNameDefaultsDataProvider()
    {
        $layout = '<layout>
            <catalogsearch_test>
                <block type="test/test">
                    <block type="child/test"></block>
                </block>
            </catalogsearch_test>
        </layout>';
        $layout = simplexml_load_string($layout, 'Varien_Simplexml_Element');
        $block = $layout->xpath('catalogsearch_test/block/block');
        $block = $block[0];
        return array(
            array(
                simplexml_load_string('<node module="Notexisting_Module">test</node>', 'Varien_Simplexml_Element'),
                'core'
            ),
            array($block, 'core'),
        );
    }
}
