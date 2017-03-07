<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Product;

/**
 * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
 */
class ViewTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoConfigFixture current_store catalog/seo/product_canonical_tag 1
     */
    public function testViewActionWithCanonicalTag()
    {
        $this->markTestSkipped(
            'MAGETWO-40724: Canonical url from tests sometimes does not equal canonical url from action'
        );
        $this->dispatch('catalog/product/view/id/1/');

        $this->assertContains(
            '<link  rel="canonical" href="http://localhost/index.php/catalog/product/view/_ignore_category/1/id/1/" />',
            $this->getResponse()->getBody()
        );
    }
}
