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
        $this->assertContains('{"options":[],"total":0}', $responseBody);
    }
}
