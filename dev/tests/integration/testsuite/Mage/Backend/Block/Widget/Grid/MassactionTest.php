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
 * @package     Mage_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoDataFixture Mage/Backend/Block/_files/backend_theme.php
 */
class Mage_Backend_Block_Widget_Grid_MassactionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Block_Widget_Grid_Massaction
     */
    protected $_block;

    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layout;

    protected function setUp()
    {
        $this->_setFixtureTheme();

        $this->_layout = Mage::getModel('Mage_Core_Model_Layout', array('area' => 'adminhtml'));
        $this->_layout->getUpdate()->load('layout_test_grid_handle');
        $this->_layout->generateXml();
        $this->_layout->generateElements();

        $this->_block = $this->_layout->getBlock('admin.test.grid.massaction');
    }

    /**
     * Set fixture theme for admin backend area
     */
    protected function _setFixtureTheme()
    {
        Magento_Test_Bootstrap::getInstance()->reinitialize(array(
            Mage_Core_Model_App::INIT_OPTION_SCOPE_TYPE => 'store',
            Mage_Core_Model_App::INIT_OPTION_SCOPE_CODE => 'admin',
            Mage_Core_Model_App::INIT_OPTION_DIRS => array(
                Mage_Core_Model_Dir::THEMES => __DIR__ . '/../../_files/design'
            ),
        ));

        Mage::app()->getConfig()->setNode(
            'adminhtml/' . Mage_Core_Model_Design_Package::XML_PATH_THEME,
            'test/default'
        );
    }

    protected function tearDown()
    {
        unset($this->_layout);
        unset($this->_block);
    }

    /**
     * @covers Mage_Backend_Block_Widget_Grid_Massaction::getItems
     * @covers Mage_Backend_Block_Widget_Grid_Massaction::getCount
     * @covers Mage_Backend_Block_Widget_Grid_Massaction::getItemsJson
     * @covers Mage_Backend_Block_Widget_Grid_Massaction::isAvailable
     */
    public function testMassactionDefaultValues()
    {
        /** @var $blockEmpty Mage_Backend_Block_Widget_Grid_Massaction */
        $blockEmpty = Mage::app()->getLayout()->createBlock('Mage_Backend_Block_Widget_Grid_Massaction');
        $this->assertEmpty($blockEmpty->getItems());
        $this->assertEquals(0, $blockEmpty->getCount());
        $this->assertSame('[]', $blockEmpty->getItemsJson());

        $this->assertFalse($blockEmpty->isAvailable());
    }

    public function testGetJavaScript()
    {
        $javascript = $this->_block->getJavaScript();

        $expectedItemFirst = '#"option_id1":{"label":"Option One",'
            . '"url":"http:\\\/\\\/localhost\\\/index\.php\\\/(?:key\\\/([\w\d]+)\\\/)?",'
            . '"complete":"Test","id":"option_id1"}#';
        $this->assertRegExp($expectedItemFirst, $javascript);

        $expectedItemSecond = '#"option_id2":{"label":"Option Two",'
            . '"url":"http:\\\/\\\/localhost\\\/index\.php\\\/(?:key\\\/([\w\d]+)\\\/)?",'
            . '"confirm":"Are you sure\?","id":"option_id2"}#';
        $this->assertRegExp($expectedItemSecond, $javascript);
    }

    public function testGetJavaScriptWithAddedItem()
    {
        $input = array(
            'id' => 'option_id3',
            'label' => 'Option Three',
            'url' => '*/*/option3',
            'block_name' => 'admin.test.grid.massaction.option3'
        );
        $expected = '#"option_id3":{"id":"option_id3","label":"Option Three",'
            . '"url":"http:\\\/\\\/localhost\\\/index\.php\\\/(?:key\\\/([\w\d]+)\\\/)?",'
            . '"block_name":"admin.test.grid.massaction.option3"}#';

        $this->_block->addItem($input['id'], $input);
        $this->assertRegExp($expected, $this->_block->getJavaScript());
    }

    public function testGetCount()
    {
        $this->assertEquals(2, $this->_block->getCount());
    }

    /**
     * @param $itemId
     * @param $expectedItem
     * @dataProvider getItemsDataProvider
     */
    public function testGetItems($itemId, $expectedItem)
    {
        $items = $this->_block->getItems();
        $this->assertCount(2, $items);
        $this->assertArrayHasKey($itemId, $items);

        $actualItem = $items[$itemId];
        $this->assertEquals($expectedItem['id'], $actualItem->getId());
        $this->assertEquals($expectedItem['label'], $actualItem->getLabel());
        $this->assertRegExp($expectedItem['url'], $actualItem->getUrl());
        $this->assertEquals($expectedItem['selected'], $actualItem->getSelected());
        $this->assertEquals($expectedItem['blockname'], $actualItem->getBlockName());
    }

    /**
     * @return array
     */
    public function getItemsDataProvider()
    {
        return array(
            array(
                'option_id1',
                array(
                    'id' => 'option_id1',
                    'label' => 'Option One',
                    'url' => '#http:\/\/localhost\/index\.php\/(?:key\/([\w\d]+)\/)?#',
                    'selected' => false,
                    'blockname' => ''
                )
            ),
            array(
                'option_id2',
                array(
                    'id' => 'option_id2',
                    'label' => 'Option Two',
                    'url' => '#http:\/\/localhost\/index\.php\/(?:key\/([\w\d]+)\/)?#',
                    'selected' => false,
                    'blockname' => ''
                )
            )
        );
    }

    public function testGridContainsMassactionColumn()
    {
        $this->_layout->getBlock('admin.test.grid')->toHtml();

        $gridMassactionColumn = $this->_layout->getBlock('admin.test.grid')
            ->getColumnSet()
            ->getChildBlock('massaction');
        $this->assertNotNull($gridMassactionColumn, 'Massaction column does not exist in the grid column set');
        $this->assertInstanceOf(
            'Mage_Backend_Block_Widget_Grid_Column',
            $gridMassactionColumn,
            'Massaction column is not an instance of Mage_Backend_Block_Widget_Column'
        );
    }
}
