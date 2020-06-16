<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Catalog\Controller\Product (downloadable product type)
 */
namespace Magento\Downloadable\Controller;

class ProductTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testViewAction()
    {
        $this->dispatch('catalog/product/view/id/1');
        $responseBody = $this->getResponse()->getBody();
        $this->assertStringContainsString('Downloadable Product', $responseBody);
        $this->assertStringContainsString('In stock', $responseBody);
        $this->assertStringContainsString('Add to Cart', $responseBody);
        $actualLinkCount = substr_count($responseBody, 'Downloadable Product Link');
        $this->assertEquals(1, $actualLinkCount, 'Downloadable product link should appear on the page exactly once.');
    }
}
