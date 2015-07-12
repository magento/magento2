<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller;

class ProductTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @dataProvider listActionDesignDataProvider
     */
    public function testListActionDesign($productId, $expectedDesign)
    {
        $this->getRequest()->setParam('id', $productId);
        $this->dispatch('review/product/listAction');
        $result = $this->getResponse()->getBody();
        $this->assertContains("static/frontend/{$expectedDesign}/en_US/Magento_Theme/favicon.ico", $result);
    }

    /**
     * @return array
     */
    public function listActionDesignDataProvider()
    {
        return ['custom product design' => [2, 'Magento/blank']];
    }
}
