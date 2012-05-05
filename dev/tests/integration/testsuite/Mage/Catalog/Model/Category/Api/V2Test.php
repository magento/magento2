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
 * Test class for Mage_Catalog_Model_Category_Api_V2.
 */
class Mage_Catalog_Model_Category_Api_V2Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Category_Api_V2
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Category_Api_V2;
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
    }

    public function testCRUD()
    {
        // @codingStandardsIgnoreStart
        $category = new stdClass();
        $category->name                 = 'test category';
        $category->available_sort_by    = 'name';
        $category->default_sort_by      = 'name';
        $category->is_active            = 1;
        $category->include_in_menu      = 1;
        // @codingStandardsIgnoreEnd

        $categoryId = $this->_model->create(1, $category);
        $this->assertNotEmpty($categoryId);
        $data = $this->_model->info($categoryId);
        $this->assertNotEmpty($data);
        $this->assertEquals($category->name, $data['name']);
        // @codingStandardsIgnoreStart
        $this->assertEquals($category->default_sort_by, $data['default_sort_by']);
        $this->assertEquals($category->is_active, $data['is_active']);
        // @codingStandardsIgnoreEnd

        $category->name = 'new name';
        $this->_model->update($categoryId, $category);
        $data = $this->_model->info($categoryId);
        $this->assertNotEmpty($data);
        $this->assertEquals($category->name, $data['name']);

        $this->assertTrue($this->_model->delete($categoryId));
    }

}
