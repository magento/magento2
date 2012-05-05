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
 * Test class for Mage_Catalog_Seo_SitemapController.
 *
 * @magentoDataFixture Mage/Catalog/_files/categories.php
 */
class Mage_Catalog_Seo_SitemapControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    public function testCategoryAction()
    {
        $this->dispatch('catalog/seo_sitemap/category/');

        $responseBody = $this->getResponse()->getBody();

        /* General content */
        $this->assertContains('<h1>Categories</h1>', $responseBody);
        $this->assertContains('5 Item(s)', $responseBody);

        /* Sitemap content */
        $matchesCount = preg_match('#<ul class="sitemap">.+?</ul>#s', $responseBody, $matches);
        $this->assertEquals(1, $matchesCount);
        $listHtml = $matches[0];

        $this->assertContains('Category 1', $listHtml);
        $this->assertContains('Category 1.1', $listHtml);
        $this->assertContains('Category 1.1.1', $listHtml);
        $this->assertContains('Category 2', $listHtml);
        $this->assertContains('Movable', $listHtml);

        $this->assertContains('http://localhost/index.php/category-1.html', $listHtml);

        $this->markTestIncomplete('Bug MAGETWO-144');

        $this->assertContains('http://localhost/index.php/category-1/category-1-1.html', $listHtml);
        $this->assertContains('http://localhost/index.php/category-1/category-1-1/category-1-1-1.html', $listHtml);
        $this->assertContains('http://localhost/index.php/category-2.html', $listHtml);
        $this->assertContains('http://localhost/index.php/movable.html', $listHtml);
    }

    /**
     * @magentoConfigFixture current_store catalog/sitemap/tree_mode 1
     */
    public function testCategoryActionTreeMode()
    {
        $this->dispatch('catalog/seo_sitemap/category/');

        /* Layout updates */
        $handles = Mage::app()->getLayout()->getUpdate()->getHandles();
        $this->assertContains('catalog_seo_sitemap_category_type_tree', $handles);
    }

    public function testProductAction()
    {
        $this->dispatch('catalog/seo_sitemap/product/');

        $responseBody = $this->getResponse()->getBody();

        /* General content */
        $this->assertContains('<h1>Products</h1>', $responseBody);
        $this->assertContains('2 Item(s)', $responseBody);

        /* Sitemap content */
        $matchesCount = preg_match('#<ul class="sitemap">.+?</ul>#s', $responseBody, $matches);
        $this->assertEquals(1, $matchesCount);
        $listHtml = $matches[0];

        $this->assertContains('Simple Product', $listHtml);
        $this->assertContains('Simple Product Two', $listHtml);

        $this->assertContains('http://localhost/index.php/simple-product.html', $listHtml);
        $this->assertContains('http://localhost/index.php/simple-product-two.html', $listHtml);
    }
}
