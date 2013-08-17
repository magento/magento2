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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Backend_Model_Url
 */
class Mage_Backend_Block_Widget_GridTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_Helper_ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new Magento_Test_Helper_ObjectManager($this);
    }

    /**
     * @covers Mage_Backend_Block_Widget_Grid::addRssList
     * @covers Mage_Backend_Block_Widget_Grid::clearRss
     * @covers Mage_Backend_Block_Widget_Grid::getRssLists
     * @dataProvider addGetClearRssDataProvider
     */
    public function testAddGetClearRss($isUseStoreInUrl, $setStoreCount)
    {
        $urlMock = $this->getMock('Mage_Core_Model_Url', array(), array(), '', false);
        $urlMock->expects($this->at($setStoreCount))->method('setStore');
        $urlMock->expects($this->any())->method('getUrl')->will($this->returnValue('some_url'));

        $storeMock = $this->getMock('Mage_Core_Model_Store', array('isUseStoreInUrl'), array(), '', false);
        $storeMock->expects($this->any())->method('isUseStoreInUrl')->will($this->returnValue($isUseStoreInUrl));
        $storeManager = $this->getMock('Mage_Core_Model_StoreManagerInterface');
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        /** @var $block Mage_Backend_Block_Widget_Grid */
        $block = $this->_objectManager->getObject(
            'Mage_Backend_Block_Widget_Grid',
            array(
                'storeManager' => $storeManager,
                'urlModel' => $urlMock,
            )
        );

        $this->assertFalse($block->getRssLists());

        $block->addRssList('some_url', 'some_label');
        $elements = $block->getRssLists();
        $element = reset($elements);
        $this->assertEquals('some_url', $element->getUrl());
        $this->assertEquals('some_label', $element->getLabel());

        $block->clearRss();
        $this->assertFalse($block->getRssLists());
    }

    /**
     * @see self::testAddGetClearRss()
     * @return array
     */
    public function addGetClearRssDataProvider()
    {
         return array(
            array(true, 1),
            array(false, 0),
        );
    }
}
