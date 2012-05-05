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
 * @package     Mage_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Catalog_Block_Product_Abstract.
 *
 * @magentoDataFixture Mage/Catalog/_files/product_simple.php
 * @magentoDataFixture Mage/Catalog/_files/product_image.php
 */
class Mage_Catalog_Block_Product_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Block_Product_Abstract
     */
    protected $_block;

    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    protected function setUp()
    {
        $this->_block = $this->getMockForAbstractClass('Mage_Catalog_Block_Product_Abstract');
        $this->_product = new Mage_Catalog_Model_Product();
        $this->_product->load(1);
        $this->_product->addData(array(
            'image'       => '/m/a/magento_image.jpg',
            'small_image' => '/m/a/magento_image.jpg',
            'thumbnail'   => '/m/a/magento_image.jpg',
        ));
        $this->_block->setProduct($this->_product);
    }

    public function testGetAddToCartUrl()
    {
        $url = $this->_block->getAddToCartUrl($this->_product);
        $this->assertStringEndsWith('?options=cart', $url);
        $this->assertStringMatchesFormat('%ssimple-product.html%s', $url);
    }

    public function testGetSubmitUrl()
    {
        /* by default same as add to cart */
        $this->assertStringEndsWith('?options=cart', $this->_block->getSubmitUrl($this->_product));
        $this->_block->setData('submit_route_data', array('route' => 'catalog/product/view'));
        $this->assertStringEndsWith('catalog/product/view/', $this->_block->getSubmitUrl($this->_product));
    }

    public function testGetAddToWishlistUrl()
    {
        $this->assertStringEndsWith(
            'wishlist/index/add/product/1/',
            $this->_block->getAddToWishlistUrl($this->_product)
        );
    }

    public function testGetAddToCompareUrl()
    {
        $this->assertStringMatchesFormat(
            '%scatalog/product_compare/add/product/1/%s',
            $this->_block->getAddToCompareUrl($this->_product)
        );
    }

    public function testGetMinimalQty()
    {
        $this->assertGreaterThan(0, $this->_block->getMinimalQty($this->_product));
    }

    public function testGetPriceHtml()
    {
        $this->_block->setLayout(new Mage_Core_Model_Layout());
        $this->assertContains('10', $this->_block->getPriceHtml($this->_product));
    }

    public function testGetReviewsSummaryHtml()
    {
        $this->_block->setLayout(new Mage_Core_Model_Layout());
        $html = $this->_block->getReviewsSummaryHtml($this->_product, false, true);
        $this->assertNotEmpty($html);
        $this->assertContains('review', $html);
    }

    public function testGetProduct()
    {
        $this->assertSame($this->_product, $this->_block->getProduct());
    }

    public function testGetTierPriceTemplate()
    {
        $this->assertEquals('product/view/tierprices.phtml', $this->_block->getTierPriceTemplate());
        $this->_block->setData('tier_price_template', 'test.phtml');
        $this->assertEquals('test.phtml', $this->_block->getTierPriceTemplate());
    }

    public function testGetTierPriceHtml()
    {
        $this->_block->setLayout(new Mage_Core_Model_Layout());
        $html = $this->_block->getTierPriceHtml();
        $this->assertNotEmpty($html);
        $this->assertContains('2', $html); /* Buy 2 */
        $this->assertContains('8', $html); /* Price 8 */
        $this->assertContains('5', $html); /* Buy 5 and price 5 */

    }

    public function testGetTierPrices()
    {
        $prices = $this->_block->getTierPrices();
        $this->assertNotEmpty($prices);
        $this->assertGreaterThanOrEqual(2, count($prices));
        $this->assertArrayHasKey('price', $prices[0]);
        $this->assertArrayHasKey('savePercent', $prices[0]);
        $this->assertArrayHasKey('formated_price', $prices[0]);
        $this->assertArrayHasKey('formated_price_incl_tax', $prices[0]);

        $this->_product->setFinalPrice(7);
        $prices = $this->_block->getTierPrices();
        $this->assertEquals(1, count($prices));
    }

    public function testGetImageLabel()
    {
        $this->assertEquals($this->_product->getName(), $this->_block->getImageLabel());
    }

    public function testGetProductUrl()
    {
        $this->assertStringEndsWith('simple-product.html', $this->_block->getProductUrl($this->_product));
    }

    public function testHasProductUrl()
    {
        $this->assertTrue($this->_block->hasProductUrl($this->_product));
    }

    public function testLayoutDependColumnCount()
    {
        $this->_block->setLayout(new Mage_Core_Model_Layout());
        $this->assertEquals(3, $this->_block->getColumnCount()); /* default column count */

        $this->_block->addColumnCountLayoutDepend('test', 10);
        $this->assertEquals(10, $this->_block->getColumnCountLayoutDepend('test'));
        $this->_block->removeColumnCountLayoutDepend('test');
        $this->assertFalse($this->_block->getColumnCountLayoutDepend('test'));
    }

    public function testGetCanShowProductPrice()
    {
        $this->assertTrue($this->_block->getCanShowProductPrice($this->_product));
    }

    public function testThumbnail()
    {
        $size = $this->_block->getThumbnailSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/'.$size, $this->_block->getThumbnailUrl($this->_product));
    }

    public function testThumbnailSidebar()
    {
        $size = $this->_block->getThumbnailSidebarSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/'.$size, $this->_block->getThumbnailSidebarUrl($this->_product));
    }

    public function testSmallImage()
    {
        $size = $this->_block->getSmallImageSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/'.$size, $this->_block->getSmallImageUrl($this->_product));
    }

    public function testSmallImageSidebar()
    {
        $size = $this->_block->getSmallImageSidebarSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/'.$size, $this->_block->getSmallImageSidebarUrl($this->_product));
    }

    public function testBaseImage()
    {
        $size = $this->_block->getBaseImageSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/'.$size, $this->_block->getBaseImageUrl($this->_product));
    }

    public function testBaseImageIcon()
    {
        $size = $this->_block->getBaseImageIconSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/'.$size, $this->_block->getBaseImageIconUrl($this->_product));
    }
}

