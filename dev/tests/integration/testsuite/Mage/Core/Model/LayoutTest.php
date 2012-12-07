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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layout integration tests
 *
 * @magentoDbIsolation enabled
 * @group module::Mage_Layout_Merge
 */
class Mage_Core_Model_LayoutTest extends Mage_Core_Model_LayoutTestBase
{
    /**
     * @param array $inputArguments
     * @param string $expectedArea
     * @dataProvider constructorDataProvider
     */
    public function testConstructor(array $inputArguments, $expectedArea)
    {
        $layout = Mage::getModel('Mage_Core_Model_Layout', $inputArguments);
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
        $layout = Mage::getModel('Mage_Core_Model_Layout', array('structure' => $structure));
        $this->assertTrue($layout->hasElement('test.container'));
    }

    public function testDestructor()
    {
        $this->_layout->addBlock('Mage_Core_Block_Text', 'test');
        $this->assertNotEmpty($this->_layout->getAllBlocks());
        $this->_layout->__destruct();
        $this->assertEmpty($this->_layout->getAllBlocks());
    }

    public function testGetUpdate()
    {
        $this->assertInstanceOf('Mage_Core_Model_Layout_Merge', $this->_layout->getUpdate());
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
     * @magentoConfigFixture default_store design/theme/full_name test/default
     */
    public function testGenerateXmlAndElements()
    {
        $this->_layout->generateXml();
        /**
         * Generate fixture
         * file_put_contents(dirname(__FILE__) . '/_files/_layout_update.xml', $this->_model->getNode()->asNiceXml());
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
            'root_schedule_block',
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

        $this->assertFalse($this->_layout->getBlock('test.nonexisting.block'));
    }

    /**
     * @magentoConfigFixture default_store design/theme/full_name test/default
     */
    public function testLayoutDirectives()
    {
        /**
         * Test move with the same alias
         */
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        $layout->getUpdate()->load(array('layout_test_handle_move_the_same_alias'));
        $layout->generateXml()->generateElements();
        $this->assertEquals('container1', $layout->getParentName('no_name3'));

        /**
         * Test move with a new alias
         */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        $layout->getUpdate()->load(array('layout_test_handle_move_new_alias'));
        $layout->generateXml()->generateElements();
        $this->assertEquals('new_alias', $layout->getElementAlias('no_name3'));

        /**
         * Test layout action with anonymous parent block
         */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        $layout->getUpdate()->load(array('layout_test_handle_action_for_anonymous_parent_block'));
        $layout->generateXml()->generateElements();
        $this->assertEquals('schedule_block', $layout->getParentName('test.block.insert'));
        $this->assertEquals('schedule_block_1', $layout->getParentName('test.block.append'));

        /**
         * Test layout remove directive
         */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        $layout->getUpdate()->load(array('layout_test_handle_remove'));
        $layout->generateXml()->generateElements();
        $this->assertFalse($layout->getBlock('no_name2'));
        $this->assertFalse($layout->getBlock('child_block1'));
        $this->assertTrue($layout->isBlock('child_block2'));

        /**
         * Test correct move
         */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        $layout->getUpdate()->load(array('layout_test_handle_move'));
        $layout->generateXml()->generateElements();
        $this->assertEquals('container2', $layout->getParentName('container1'));
        $this->assertEquals('container1', $layout->getParentName('no.name2'));
        $this->assertEquals('block_container', $layout->getParentName('no_name3'));

        // verify `after` attribute
        $this->assertEquals('block_container', $layout->getParentName('no_name'));
        $childrenOrderArray = array_keys($layout->getChildBlocks($layout->getParentName('no_name')));
        $positionAfter = array_search('child_block1', $childrenOrderArray);
        $positionToVerify = array_search('no_name', $childrenOrderArray);
        $this->assertEquals($positionAfter, --$positionToVerify);

        // verify `before` attribute
        $this->assertEquals('block_container', $layout->getParentName('no_name4'));
        $childrenOrderArray = array_keys($layout->getChildBlocks($layout->getParentName('no_name4')));
        $positionBefore = array_search('child_block2', $childrenOrderArray);
        $positionToVerify = array_search('no_name4', $childrenOrderArray);
        $this->assertEquals($positionBefore, ++$positionToVerify);
    }

    /**
     * @magentoConfigFixture default_store design/theme/full_name test/default
     * @expectedException Magento_Exception
     */
    public function testLayoutMoveDirectiveBroken()
    {
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        $layout->getUpdate()->load(array('layout_test_handle_move_broken'));
        $layout->generateXml()->generateElements();
    }

    /**
     * @magentoConfigFixture default_store design/theme/full_name test/default
     * @expectedException Magento_Exception
     */
    public function testLayoutMoveAliasBroken()
    {
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        $layout->getUpdate()->load(array('layout_test_handle_move_alias_broken'));
        $layout->generateXml()->generateElements();
    }

    /**
     * @magentoConfigFixture default_store design/theme/full_name test/default
     * @expectedException Magento_Exception
     */
    public function testGenerateElementsBroken()
    {
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        $layout->getUpdate()->load('layout_test_handle_remove_broken');
        $layout->generateXml()->generateElements();
    }

    public function testRenderElement()
    {
        $utility = new Mage_Core_Utility_Layout($this);
        $layout = $utility->getLayoutFromFixture(__DIR__ . '/_files/valid_layout_updates.xml',
            $utility->getLayoutDependencies()
        );
        $layout->getUpdate()->load(array('first_handle', 'a_handle', 'another_handle'));
        $layout->generateXml()->generateElements();
        $this->assertEmpty($layout->renderElement('nonexisting_element'));
        $this->assertEquals("Value: 1 Reference: 1.1\nValue: 2 Reference: 2.2\n", $layout->renderElement('container1'));
        $this->assertEquals("Value: 1 Reference: 1.1\n", $layout->renderElement('block1'));
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
        $expectedBlock = Mage::app()->getLayout()->createBlock('Mage_Core_Block_Text');

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
                array('type' => 'Mage_Core_Block_Template', 'is_anonymous' => false),
                '/^some_block_name_full_class$/'
            ),
            'no name block' => array(
                'Mage_Core_Block_Text_List',
                '',
                array(
                    'type' => 'Mage_Core_Block_Text_List',
                    'key1' => 'value1',
                ),
                '/text_list/'
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
        $block2 = Mage::getObjectManager()->create('Mage_Core_Block_Text');
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
        $layout = $utility->getLayoutFromFixture(__DIR__ . '/_files/sort_special_cases.xml',
            $utility->getLayoutDependencies()
        );
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

    public function testGetBlock()
    {
        $this->assertFalse($this->_layout->getBlock('test'));
        $block = Mage::app()->getLayout()->createBlock('Mage_Core_Block_Text');
        $this->_layout->setBlock('test', $block);
        $this->assertSame($block, $this->_layout->getBlock('test'));
    }

    /**
     * Invoke getBlock() while layout is being generated
     *
     * Assertions in this test are pure formalism. The point is to emulate situation where block refers to other block
     * while the latter hasn't been generated yet, and assure that there is no crash
     */
    public function testGetBlockUnscheduled()
    {
        $utility = new Mage_Core_Utility_Layout($this);
        $layout = $utility->getLayoutFromFixture(__DIR__ . '/_files/valid_layout_updates.xml',
            $utility->getLayoutDependencies()
        );
        $layout->getUpdate()->load(array('get_block_special_case'));
        $layout->generateXml()->generateElements();
        $this->assertInstanceOf('Mage_Core_Block_Text', $layout->getBlock('block1'));
        $this->assertInstanceOf('Mage_Core_Block_Text', $layout->getBlock('block2'));
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testGetBlockUnscheduledException()
    {
        $utility = new Mage_Core_Utility_Layout($this);
        $layout = $utility->getLayoutFromFixture(__DIR__ . '/_files/valid_layout_updates.xml',
            $utility->getLayoutDependencies()
        );
        $layout->getUpdate()->load(array('get_block_special_case_exception'));
        $layout->generateXml();
        $layout->generateElements();
    }

    public function testGetParentName()
    {
        /**
         * Test get name
         */
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
