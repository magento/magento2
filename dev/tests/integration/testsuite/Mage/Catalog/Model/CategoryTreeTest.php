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
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Catalog_Model_Category.
 * - tree knowledge is tested
 *
 * @see Mage_Catalog_Model_CategoryTest
 * @magentoDataFixture Mage/Catalog/_files/categories.php
 */
class Mage_Catalog_Model_CategoryTreeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Category
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Category();
    }

    public function testMove()
    {
        $this->_model->load(7);
        $this->assertEquals(2, $this->_model->getParentId());
        $this->_model->move(6, 0);
        /* load is not enough to reset category data */
        $this->_model->setData(array());
        $this->_model->load(7);
        $this->assertEquals(6, $this->_model->getParentId());
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testMoveWrongParent()
    {
        $this->_model->load(7);
        $this->_model->move(100, 0);
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testMoveWrongId()
    {
        $this->_model->move(100, 0);
    }

    public function testGetUrlPath()
    {
        $this->assertNull($this->_model->getUrlPath());
        $this->_model->load(5);
        $this->assertEquals('category-1/category-1-1/category-1-1-1.html', $this->_model->getUrlPath());
    }

    public function testGetParentCategory()
    {
        $category = $this->_model->getParentCategory();
        $this->assertInstanceOf('Mage_Catalog_Model_Category', $category);
        $this->assertSame($category, $this->_model->getParentCategory());
    }

    public function testGetParentId()
    {
        $this->assertEquals(0, $this->_model->getParentId());
        $this->_model->unsetData();
        $this->_model->load(4);
        $this->assertEquals(3, $this->_model->getParentId());
    }

    public function testGetParentIds()
    {
        $this->assertEquals(array(), $this->_model->getParentIds());
        $this->_model->unsetData();
        $this->_model->load(4);
        $this->assertContains(3, $this->_model->getParentIds());
        $this->assertNotContains(4, $this->_model->getParentIds());
    }

    public function testGetChildren()
    {
        $this->_model->load(3);
        $this->assertEquals('4', $this->_model->getChildren());
    }

    public function testGetPathInStore()
    {
        $this->_model->load(5);
        $this->assertEquals('5,4,3', $this->_model->getPathInStore());
    }

    public function testGetAllChildren()
    {
        $this->_model->load(4);
        $this->assertEquals('4,5', $this->_model->getAllChildren());
        $this->_model->load(5);
        $this->assertEquals('5', $this->_model->getAllChildren());
    }

    public function testGetPathIds()
    {
        $this->assertEquals(array(''), $this->_model->getPathIds());
        $this->_model->setPathIds(array(1));
        $this->assertEquals(array(1), $this->_model->getPathIds());

        $this->_model->unsetData();
        $this->_model->setPath('1/2/3');
        $this->assertEquals(array(1,2,3), $this->_model->getPathIds());
    }

    public function testGetLevel()
    {
        $this->assertEquals(0, $this->_model->getLevel());
        $this->_model->setData('level', 1);
        $this->assertEquals(1, $this->_model->getLevel());
    }

    public function testGetAnchorsAbove()
    {
        $this->_model->load(4);
        $this->assertEmpty($this->_model->getAnchorsAbove());
        $this->_model->load(5);
        $this->assertContains(4, $this->_model->getAnchorsAbove());
    }

    public function testGetParentCategories()
    {
        $this->_model->load(5);
        $parents = $this->_model->getParentCategories();
        $this->assertEquals(3, count($parents));
    }

    public function testGetParentCategoriesEmpty()
    {
        $this->_model->load(1);
        $parents = $this->_model->getParentCategories();
        $this->assertEquals(0, count($parents));
    }


    public function testGetChildrenCategories()
    {
        $this->_model->load(3);
        $children = $this->_model->getChildrenCategories();
        $this->assertEquals(1, count($children));
    }

    public function testGetChildrenCategoriesEmpty()
    {
        $this->_model->load(5);
        $children = $this->_model->getChildrenCategories();
        $this->assertEquals(0, count($children));
    }

    public function testGetParentDesignCategory()
    {
        $this->_model->load(5);
        $parent = $this->_model->getParentDesignCategory();
        $this->assertEquals(5, $parent->getId());
    }

    public function testIsInRootCategoryList()
    {
        $this->assertFalse($this->_model->isInRootCategoryList());
        $this->_model->unsetData();
        $this->_model->load(3);
        $this->assertTrue($this->_model->isInRootCategoryList());
    }
}
