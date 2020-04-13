<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

/**
 * @magentoAppArea adminhtml
 */
class SearchTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testExecute() : void
    {
        $this->getRequest()
            ->setPostValue('searchKey', 'simple')
            ->setPostValue('page', 1)
            ->setPostValue('limit', 50);
        $this->dispatch('backend/catalog/product/search');
        $responseBody = $this->getResponse()->getBody();
        $this->assertContains(
            '"options":{"1":{"value":"1","label":"Simple Product","is_active":1,"path":"simple","optgroup":false}',
            $responseBody
        );
    }

    public function testExecuteNonExistingSearchKey() : void
    {
        $this->getRequest()
            ->setPostValue('searchKey', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
            ->setPostValue('page', 1)
            ->setPostValue('limit', 50);
        $this->dispatch('backend/catalog/product/search');
        $responseBody = $this->getResponse()->getBody();
        $jsonResponse = json_decode($responseBody, true);
        $this->assertEmpty($jsonResponse['options']);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testExecuteNotVisibleIndividuallyProducts() : void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

        $product = $productRepository->get('simple-3');
        $this->assertEquals('simple-3', $product->getSku());
        $this->getRequest()
            ->setPostValue('searchKey', 'simple-3')
            ->setPostValue('page', 1)
            ->setPostValue('limit', 50);
        $this->dispatch('backend/catalog/product/search');
        $responseBody = $this->getResponse()->getBody();
        $jsonResponse = json_decode($responseBody, true);
        $this->assertEquals(1, $jsonResponse['total']);
        $this->assertCount(1, $jsonResponse['options']);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_mixed_products.php
     */
    public function testExecuteEnabledAndDisabledProducts() : void
    {
        $this->getRequest()
            ->setPostValue('searchKey', 'simple')
            ->setPostValue('page', 1)
            ->setPostValue('limit', 50);
        $this->dispatch('backend/catalog/product/search');
        $responseBody = $this->getResponse()->getBody();
        $jsonResponse = json_decode($responseBody, true);
        $this->assertEquals(6, $jsonResponse['total']);
        $this->assertCount(6, $jsonResponse['options']);
    }
}
