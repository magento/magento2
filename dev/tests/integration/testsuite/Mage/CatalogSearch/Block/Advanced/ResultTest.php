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
 * @package     Mage_CatalogSearch
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_CatalogSearch
 */
class Mage_CatalogSearch_Block_Advanced_ResultTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layout;

    /**
     * @var Mage_CatalogSearch_Block_Advanced_Result
     */
    protected $_block;

    protected function setUp()
    {
        $this->_layout = new Mage_Core_Model_Layout;
        $this->_block = $this->_layout->createBlock('Mage_CatalogSearch_Block_Advanced_Result', 'block');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testSetListOrders()
    {
        $sortOptions = array(
            'option1' => 'Label Option 1',
            'position' => 'Label Position',
            'option3' => 'Label Option 2'
        );
        $category = $this->getMock('Mage_Catalog_Model_Category', array('getAvailableSortByOptions'));
        $category->expects($this->atLeastOnce())
            ->method('getAvailableSortByOptions')
            ->will($this->returnValue($sortOptions));
        $category->setId(rand(1, 1000)); // Any id - just for layer navigation
        Mage::getSingleton('Mage_Catalog_Model_Layer')->setCurrentCategory($category);

        $childBlock = $this->_layout->addBlock('Mage_Core_Block_Text', 'search_result_list', 'block');

        $expectedOptions = array(
            'option1' => 'Label Option 1',
            'option3' => 'Label Option 2'
        );
        $this->assertNotEquals($expectedOptions, $childBlock->getAvailableOrders());
        $this->_block->setListOrders();
        $this->assertEquals($expectedOptions, $childBlock->getAvailableOrders());
    }

    public function testSetListModes()
    {
        $childBlock = $this->_layout->addBlock('Mage_Core_Block_Text', 'search_result_list', 'block');
        $this->assertEmpty($childBlock->getModes());
        $this->_block->setListModes();
        $this->assertNotEmpty($childBlock->getModes());
    }

    public function testSetListCollection()
    {
        $childBlock = $this->_layout->addBlock('Mage_Core_Block_Text', 'search_result_list', 'block');
        $this->assertEmpty($childBlock->getCollection());
        $this->_block->setListCollection();
        $this->assertInstanceOf(
            'Mage_CatalogSearch_Model_Resource_Advanced_Collection',
            $childBlock->getCollection()
        );
    }
}
