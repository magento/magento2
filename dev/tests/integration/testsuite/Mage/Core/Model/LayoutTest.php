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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Core
 */
class Mage_Core_Model_LayoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_model;

    public static function setUpBeforeClass()
    {
        /* Point application to predefined layout fixtures */
        Mage::getConfig()->setOptions(array(
            'design_dir' => __DIR__ . '/_files/design',
        ));
        Mage::getDesign()->setDesignTheme('test/default/default');

        /* Disable loading and saving layout cache */
        Mage::app()->getCacheInstance()->banUse('layout');
    }

    protected function setUp()
    {
        $this->_model = new Mage_Core_Model_Layout();
        $this->_model->getUpdate()->addHandle('layout_test_handle_main');
        $this->_model->getUpdate()->load('layout_test_handle_extra');
    }

    public function testGetUpdate()
    {
        $this->assertInstanceOf('Mage_Core_Model_Layout_Update', $this->_model->getUpdate());
    }

    public function testGetSetArea()
    {
        $this->assertEmpty($this->_model->getArea());
        $this->_model->setArea('frontend');
        $this->assertEquals('frontend', $this->_model->getArea());
    }

    public function testGetSetDirectOutput()
    {
        $this->assertFalse($this->_model->getDirectOutput());
        $this->_model->setDirectOutput(true);
        $this->assertTrue($this->_model->getDirectOutput());
    }

    /**
     * @covers Mage_Core_Model_Layout::getAllBlocks
     * @covers Mage_Core_Model_Layout::generateBlocks
     * @covers Mage_Core_Model_Layout::getBlock
     */
    public function testGenerateXmlAndBlocks()
    {
        $this->_model->generateXml();
        /* Generate fixture
        file_put_contents(__DIR__ . '/_files/_layout_update.xml', $this->_model->getNode()->asNiceXml());
        */
        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/_files/_layout_update.xml', $this->_model->getXmlString());

        $this->assertEquals(array(), $this->_model->getAllBlocks());

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
            'index_notifications',
            'index_notifications_copy'
        );
        $this->_model->generateBlocks();

        $actualBlocks = $this->_model->getAllBlocks();
        $this->assertEquals($expectedBlocks, array_keys($actualBlocks));

        /** @var $block Mage_Adminhtml_Block_Page_Head */
        $block = $this->_model->getBlock('head');
        $this->assertEquals('Magento Admin', $block->getTitle());

        $block = $this->_model->getBlock('head.calendar');
        $this->assertSame($this->_model->getBlock('head'), $block->getParentBlock());

        /** @var $block Mage_Core_Block_Template */
        $block = $this->_model->getBlock('root');
        $this->assertEquals('popup.phtml', $block->getTemplate());
    }

    public function testSetUnsetBlock()
    {
        $expectedBlockName = 'block_' . __METHOD__;
        $expectedBlock = new Mage_Core_Block_Text();

        $this->_model->setBlock($expectedBlockName, $expectedBlock);
        $this->assertSame($expectedBlock, $this->_model->getBlock($expectedBlockName));

        $this->_model->unsetBlock($expectedBlockName);
        $this->assertFalse($this->_model->getBlock($expectedBlockName));
    }

    /**
     * @dataProvider createBlockDataProvider
     */
    public function testCreateBlock($blockType, $blockName, array $blockData, $expectedName, $expectedAnonSuffix = null)
    {
        $expectedData = $blockData + array('type' => $blockType);

        $block = $this->_model->createBlock($blockType, $blockName, $blockData);

        $this->assertEquals($this->_model, $block->getLayout());
        $this->assertRegExp($expectedName, $block->getNameInLayout());
        $this->assertEquals($expectedData, $block->getData());
        $this->assertEquals($expectedAnonSuffix, $block->getAnonSuffix());
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
            'anonymous block' => array(
                'Mage_Core_Block_Text_List',
                '',
                array('type' => 'Mage_Core_Block_Text_List',
                'key1' => 'value1'),
                '/^ANONYMOUS_.+/'
            ),
            'anonymous suffix' => array(
                'Mage_Core_Block_Template',
                '.some_anonymous_suffix',
                array('type' => 'Mage_Core_Block_Template'),
                '/^ANONYMOUS_.+/',
                'some_anonymous_suffix'
            )
        );
    }

    public function testCreateBlockNotExists()
    {
        $this->assertFalse($this->_model->createBlock(''));
        $this->assertFalse($this->_model->createBlock('block_not_exists'));
    }

    /**
     * @covers Mage_Core_Model_Layout::addBlock
     * @covers Mage_Core_Model_Layout::addOutputBlock
     * @covers Mage_Core_Model_Layout::getOutput
     * @covers Mage_Core_Model_Layout::removeOutputBlock
     */
    public function testGetOutput()
    {
        $blockName = 'block_' . __METHOD__;
        $expectedText = "some_text_for_$blockName";

        $block = new Mage_Core_Block_Text();
        $block->setText($expectedText);
        $this->_model->addBlock($block, $blockName);

        $this->_model->addOutputBlock($blockName);
        $this->assertEquals($expectedText, $this->_model->getOutput());

        $this->_model->removeOutputBlock($blockName);
        $this->assertEmpty($this->_model->getOutput());
    }

    public function testGetMessagesBlock()
    {
        $this->assertInstanceOf('Mage_Core_Block_Messages', $this->_model->getMessagesBlock());
    }

    /**
     * @param string $blockType
     * @param string $expectedClassName
     * @dataProvider getBlockSingletonDataProvider
     */
    public function testGetBlockSingleton($blockType, $expectedClassName)
    {
        $block = $this->_model->getBlockSingleton($blockType);
        $this->assertInstanceOf($expectedClassName, $block);
        $this->assertSame($block, $this->_model->getBlockSingleton($blockType));
    }

    public function getBlockSingletonDataProvider()
    {
        return array(
            array('Mage_Core_Block_Text', 'Mage_Core_Block_Text')
        );
    }

    public function testHelper()
    {
        $helper = $this->_model->helper('Mage_Core_Helper_Data');
        $this->assertInstanceOf('Mage_Core_Helper_Data', $helper);
        $this->assertSame($this->_model, $helper->getLayout());
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
