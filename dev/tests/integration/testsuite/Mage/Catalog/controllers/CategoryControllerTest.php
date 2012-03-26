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
 * Test class for Mage_Catalog_CategoryController.
 *
 * @group module:Mage_Catalog
 * @magentoDataFixture Mage/Catalog/_files/categories.php
 */
class Mage_Catalog_CategoryControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    public function assert404NotFound()
    {
        parent::assert404NotFound();
        $this->assertNull(Mage::registry('current_category'));
    }

    public function getViewActionDataProvider()
    {
        return array(
            'category without children' => array(
                '$categoryId' => 5,
                '$expectedProductCount' => 1,
                array(
                    'catalog_category_view_type_default',
                    'catalog_category_view_type_default_without_children',
                ),
                array(
                    'categorypath-category-1-category-1-1-category-1-1-1-html',
                    'category-category-1-1-1',
                    '<title>Category 1.1.1 - Category 1.1 - Category 1</title>',
                    '<h1>Category 1.1.1</h1>',
                    'Simple Product Two',
                    '$45.67',
                ),
            ),
            'anchor category' => array(
                '$categoryId' => 4,
                '$expectedProductCount' => 2,
                array(
                    'catalog_category_view_type_layered',
                ),
                array(
                    'categorypath-category-1-category-1-1-html',
                    'category-category-1-1',
                    '<title>Category 1.1 - Category 1</title>',
                    '<h1>Category 1.1</h1>',
                    'Simple Product',
                    '$10.00',
                    'Simple Product Two',
                    '$45.67',
                ),
            ),
        );
    }

    /**
     * @dataProvider getViewActionDataProvider
     */
    public function testViewAction($categoryId, $expectedProductCount, array $expectedHandles, array $expectedContent)
    {
        $this->dispatch("catalog/category/view/id/$categoryId");

        /** @var $currentCategory Mage_Catalog_Model_Category */
        $currentCategory = Mage::registry('current_category');
        $this->assertInstanceOf('Mage_Catalog_Model_Category', $currentCategory);
        $this->assertEquals($categoryId, $currentCategory->getId(), 'Category in registry.');

        $lastCategoryId = Mage::getSingleton('Mage_Catalog_Model_Session')->getLastVisitedCategoryId();
        $this->assertEquals($categoryId, $lastCategoryId, 'Last visited category.');

        /* Layout updates */
        $handles = Mage::app()->getLayout()->getUpdate()->getHandles();
        foreach ($expectedHandles as $expectedHandleName) {
            $this->assertContains($expectedHandleName, $handles);
        }

        $responseBody = $this->getResponse()->getBody();

        /* Response content */
        foreach ($expectedContent as $expectedText) {
            $this->assertContains($expectedText, $responseBody);
        }

        $actualProductCount = substr_count($responseBody, '<h2 class="product-name">');
        $this->assertEquals($expectedProductCount, $actualProductCount, 'Number of products on the page.');
    }

    public function testViewActionNoCategoryId()
    {
        $this->dispatch('catalog/category/view/');

        $this->assert404NotFound();
    }

    public function testViewActionInactiveCategory()
    {
        $this->dispatch('catalog/category/view/id/8');

        $this->assert404NotFound();
    }
}
