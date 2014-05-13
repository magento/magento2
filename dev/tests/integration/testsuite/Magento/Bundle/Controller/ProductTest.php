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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Catalog\Controller\Product (bundle product type)
 */
namespace Magento\Bundle\Controller;

class ProductTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     */
    public function testViewAction()
    {
        $this->dispatch('catalog/product/view/id/3');
        $this->assertContains(
            'catalog_product_view_type_bundle',
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\View\LayoutInterface'
            )->getUpdate()->getHandles()
        );
        $responseBody = $this->getResponse()->getBody();
        $this->assertContains('Bundle Product', $responseBody);
        $this->assertContains('In stock', $responseBody);
        $addToCartCount = substr_count($responseBody, '<span>Add to Cart</span>');
        $this->assertEquals(1, $addToCartCount, '"Add to Cart" button should appear on the page exactly once.');
        $actualLinkCount = substr_count($responseBody, '>Bundle Product Items<');
        $this->assertEquals(1, $actualLinkCount, 'Bundle product options should appear on the page exactly once.');
        $this->assertNotContains('class="options-container-big"', $responseBody);
        $this->assertSelectCount('#product-options-wrapper', 1, $responseBody);
    }
}
