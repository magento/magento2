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
class GetSelectedTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testExecute() : void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

        $product = $productRepository->get('simple');
        $this->getRequest()
            ->setPostValue('productId', $product->getId());
        $this->dispatch('backend/catalog/product/getSelected');
        $responseBody = $this->getResponse()->getBody();
        $this->assertContains(
            '{"value":"1","label":"Simple Product","is_active":1,"path":"simple"}',
            $responseBody
        );
    }

    public function testExecuteNonExistingSearchKey() : void
    {
        $this->getRequest()
            ->setPostValue('productId', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $this->dispatch('backend/catalog/product/getSelected');
        $responseBody = $this->getResponse()->getBody();
        $this->assertContains('[]', $responseBody);
    }
}
