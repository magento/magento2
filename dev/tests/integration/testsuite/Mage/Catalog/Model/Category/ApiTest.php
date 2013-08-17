<?php
/**
 * Test class for Mage_Catalog_Model_Category_Api.
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @magentoDataFixture Mage/Catalog/_files/categories.php
 * @magentoDbIsolation enabled
 */
class Mage_Catalog_Model_Category_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Category_Api
     */
    protected $_model;

    /**
     * Fixture data
     *
     * @var array
     */
    protected $_fixtureData;

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_Catalog_Model_Category_Api');
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
    }

    public function testLevel()
    {
        $default = $this->_model->level();
        $this->assertNotEmpty($default);

        $forWebsite = $this->_model->level(1);
        $this->assertNotEmpty($forWebsite);

        $this->assertEquals($default, $forWebsite);
        $this->assertEquals(
            $default,
            array(
                array(
                    'category_id'   => 2,
                    'parent_id'     => 1,
                    'name'          => 'Default Category',
                    'is_active'     => 1,
                    'position'      => 1,
                    'level'         => 1
                )
            )
        );

    }

    public function testTree()
    {
        $tree = $this->_model->tree();
        $this->assertNotEmpty($tree);
        $this->assertArrayHasKey('category_id', $tree);
        $this->assertArrayHasKey('name', $tree);
        $this->assertEquals(Mage_Catalog_Model_Category::TREE_ROOT_ID, $tree['category_id']);
    }

    public function testCRUD()
    {
        $categoryId = $this->_model->create(1, array(
            'name'              => 'test category',
            'available_sort_by' => 'name',
            'default_sort_by'   => 'name',
            'is_active'         => 1,
            'include_in_menu'   => 1
        ));
        $this->assertNotEmpty($categoryId);
        $data = $this->_model->info($categoryId);
        $this->assertNotEmpty($data);
        $this->assertEquals('test category', $data['name']);

        $this->_model->update($categoryId, array(
            'name'              => 'new name',
            'available_sort_by' => 'name',
            'default_sort_by'   => 'name',
            'is_active'         => 1,
            'include_in_menu'   => 1
        ));
        $data = $this->_model->info($categoryId);
        $this->assertEquals('new name', $data['name']);

        $this->_model->delete($categoryId);
    }

    public function testMove()
    {
        $this->assertTrue($this->_model->move(7, 6, 0));
    }

    public function testAssignedProducts()
    {
        $this->assertEmpty($this->_model->assignedProducts(1));
        $this->assertEquals(
            array(array(
                'product_id' => 1,
                'type' => 'simple',
                'set' => 4,
                'sku' => 'simple',
                'position' => '1',
            )),
            $this->_model->assignedProducts(3)
        );
    }

    /**
     * @param int $categoryId
     * @param int|string $productId
     * @param string|null $identifierType
     * @dataProvider assignProductDataProvider
     */
    public function testAssignProduct($categoryId, $productId, $identifierType = null)
    {
        $this->assertEmpty($this->_model->assignedProducts($categoryId));
        $this->assertTrue($this->_model->assignProduct($categoryId, $productId, null, $identifierType));
        $this->assertNotEmpty($this->_model->assignedProducts($categoryId));
    }

    public function assignProductDataProvider()
    {
        return array(
            'product id'           => array(1, 1),
            'product sku implicit' => array(6, 'simple'),
            'product sku explicit' => array(7, 12345, 'sku'),
        );
    }

    /**
     * @depends testAssignProduct
     */
    public function testUpdateProduct()
    {
        $this->assertTrue($this->_model->updateProduct(6, 1, 2));
        $this->assertEquals(
            array(array(
                'product_id' => 1,
                'type' => 'simple',
                'set' => 4,
                'sku' => 'simple',
                'position' => '2',
            )),
            $this->_model->assignedProducts(6)
        );
    }

    /**
     * @depends testAssignProduct
     */
    public function testRemoveProduct()
    {
        $this->assertNotEmpty($this->_model->assignedProducts(6));
        $this->assertTrue($this->_model->removeProduct(6, 1));
        $this->assertEmpty($this->_model->assignedProducts(6));
    }

    /**
     * Get formatter design date
     *
     * @param string $date
     * @return string
     */
    protected function _formatActiveDesignDate($date)
    {
        list($month, $day, $year) = explode('/', $date);
        return "$year-$month-$day 00:00:00";
    }

    /**
     * Get fixture data
     *
     * @return array
     */
    protected function _getFixtureData()
    {
        if (null === $this->_fixtureData) {
            $this->_fixtureData = require dirname(__FILE__) . '/_files/category_data.php';
        }
        return $this->_fixtureData;
    }

    /**
     * Test category CRUD
     */
    public function testCrudViaHandler()
    {
        $categoryFixture = $this->_getFixtureData();

        $categoryId = $this->_testCreate($categoryFixture);
        $this->_testUpdate($categoryId, $categoryFixture);
        $this->_testRead($categoryId, $categoryFixture);
        $this->_testDelete($categoryId);
    }

    /**
     * Test category create.
     *
     * @param array $categoryFixture
     * @return int
     */
    protected function _testCreate($categoryFixture)
    {
        $categoryId = Magento_Test_Helper_Api::call(
            $this,
            'catalogCategoryCreate',
            array(
                $categoryFixture['create']['parentId'],
                (object)$categoryFixture['create']['categoryData'],
                $categoryFixture['create']['store']
            )
        );

        $this->assertEquals(
            $categoryId,
            (int)$categoryId,
            'Result of a create method is not an integer.'
        );

        $category = Mage::getModel('Mage_Catalog_Model_Category');
        $category->load($categoryId);

        //check created data
        $this->assertEquals(
            $categoryId,
            $category->getId(),
            'Category ID is not same like from API result.'
        );

        $this->assertEquals(
            $category['custom_design_from'],
            $this->_formatActiveDesignDate(
                $categoryFixture['create']['categoryData']->custom_design_from
            ),
            'Category active design date is not the same like sent to API on create.'
        );

        $this->assertEquals(
            $category['custom_design_to'],
            $this->_formatActiveDesignDate(
                $categoryFixture['create']['categoryData']->custom_design_to
            ),
            'Category active design date is not the same like sent to API on create.'
        );

        $this->assertNotEmpty(
            $category['position'],
            'Category position is empty.'
        );
        $this->assertFalse(
            array_key_exists('custom_design_apply', $category->getData()),
            'Category data item "custom_design_apply" is deprecated.'
        );

        foreach ($categoryFixture['create']['categoryData'] as $name => $value) {
            if (in_array($name, $categoryFixture['create_skip_to_check'])) {
                continue;
            }
            $this->assertEquals(
                $value,
                $category[$name],
                sprintf(
                    'Category "%s" is "%s" and not the same like sent to create "%s".',
                    $name,
                    $category[$name],
                    $value
                )
            );
        }

        return $categoryId;
    }

    /**
     * Test category read
     *
     * @param int $categoryId
     * @param array $categoryFixture
     */
    protected function _testRead($categoryId, $categoryFixture)
    {
        $categoryRead = Magento_Test_Helper_Api::call(
            $this,
            'catalogCategoryInfo',
            array('categoryId' => $categoryId, $categoryFixture['update']['store'])
        );

        $this->assertEquals(
            $categoryRead['custom_design_from'],
            $this->_formatActiveDesignDate(
                $categoryFixture['update']['categoryData']->custom_design_from
            ),
            'Category active design date is not the same like sent to API on update.'
        );

        $this->assertFalse(
            array_key_exists('custom_design_apply', $categoryRead),
            'Category data item "custom_design_apply" is deprecated.'
        );

        foreach ($categoryFixture['update']['categoryData'] as $name => $value) {
            if (in_array($name, $categoryFixture['update_skip_to_check'])) {
                continue;
            }
            $this->assertEquals(
                $value,
                $categoryRead[$name],
                sprintf('Category data with name "%s" is not the same like sent to update.', $name)
            );
        }
    }

    /**
     * Test category update
     *
     * @param int $categoryId
     * @param array $categoryFixture
     */
    protected function _testUpdate($categoryId, $categoryFixture)
    {
        $categoryFixture['update']['categoryId'] = $categoryId;
        $resultUpdated = Magento_Test_Helper_Api::call($this, 'catalogCategoryUpdate', $categoryFixture['update']);
        $this->assertTrue($resultUpdated);

        $category = Mage::getModel('Mage_Catalog_Model_Category');
        $category->load($categoryId);

        //check updated data
        $this->assertEquals(
            $category['custom_design_from'],
            $this->_formatActiveDesignDate(
                $categoryFixture['update']['categoryData']->custom_design_from
            ),
            'Category active design date is not the same like sent to API on update.'
        );

        foreach ($categoryFixture['update']['categoryData'] as $name => $value) {
            if (in_array($name, $categoryFixture['update_skip_to_check'])) {
                continue;
            }
            $this->assertEquals(
                $value,
                $category[$name],
                sprintf('Category data with name "%s" is not the same like sent to update.', $name)
            );
        }
    }

    /**
     * Test category delete
     *
     * @param int $categoryId
     */
    protected function _testDelete($categoryId)
    {
        $categoryDelete = Magento_Test_Helper_Api::call(
            $this,
            'catalogCategoryDelete',
            array('categoryId' => $categoryId)
        );
        $this->assertTrue($categoryDelete);

        $category = Mage::getModel('Mage_Catalog_Model_Category');
        $category->load($categoryId);
        $this->assertEmpty($category->getId());
    }

    /**
     * Test category bad request
     *
     * Test fault requests and vulnerability requests
     */
    public function testBadRequestViaHandler()
    {
        $categoryFixture = $this->_getFixtureData();
        $params = $categoryFixture['create'];

        /**
         * Test vulnerability SQL injection in is_active
         */
        $params['categoryData']->is_active = $categoryFixture['vulnerability']['categoryData']->is_active;

        $categoryId = Magento_Test_Helper_Api::call($this, 'catalogCategoryCreate', $params);
        $this->assertEquals(
            $categoryId,
            (int)$categoryId,
            'Category cannot created with vulnerability in is_active field'
        );

        $category = Mage::getModel('Mage_Catalog_Model_Category');
        $category->load($categoryId);

        $this->assertEquals(
            $category['is_active'],
            (int)$categoryFixture['vulnerability']['categoryData']->is_active
        );

        /**
         * Test update with empty category ID
         */
        $params = $categoryFixture['update'];
        $params['categoryId'] = 9999;
        $exception = $result = Magento_Test_Helper_Api::callWithException($this, 'catalogCategoryUpdate', $params);
        //make result like in response
        $result = array(
            'faultcode' => $exception->faultcode,
            'faultstring' => $exception->faultstring
        );

        $category->load($categoryId);
        //name must has old value
        $this->assertEquals(
            $category['name'],
            $categoryFixture['create']['categoryData']->name,
            'Category updated with empty ID.'
        );
        //"102" is code error when category is not found on update
        $this->assertInternalType('array', $result);
        $this->assertEquals(102, $result['faultcode'], 'Fault code is not right.');

        /**
         * Test vulnerability with helper usage in custom layout update
         */
        $params['categoryId'] = $categoryId;
        $params['categoryData']->custom_layout_update =
        $categoryFixture['vulnerability']['categoryData']->custom_layout_update;
        $exception = Magento_Test_Helper_Api::callWithException($this, 'catalogCategoryUpdate', $params);
        $result = array(
            'faultcode' => $exception->faultcode,
            'faultstring' => $exception->faultstring
        );

        $category->load($categoryId);

        //"103" is code error when data validation is not passed
        $this->assertInternalType('array', $result);
        $this->assertEquals(103, $result['faultcode'], 'Fault code is not right.');

    }

    /**
     * Test delete root category
     */
    public function testRootCategoryDeleteViaHandler()
    {
        $exception = Magento_Test_Helper_Api::callWithException(
            $this,
            'catalogCategoryDelete',
            array('categoryId' => Mage_Catalog_Model_Category::TREE_ROOT_ID)
        );
        $result = array(
            'faultcode' => $exception->faultcode,
            'faultstring' => $exception->faultstring
        );

        $this->assertInternalType('array', $result);
        $this->assertEquals(105, $result['faultcode'], 'Fault code is not right.');
        $this->assertEquals(
            'Cannot remove the system category.',
            $result['faultstring'],
            'Exception message is not right.'
        );

        $category = Mage::getModel('Mage_Catalog_Model_Category');
        $this->assertNotNull($category->load(Mage_Catalog_Model_Category::TREE_ROOT_ID)->getId());
    }
}
