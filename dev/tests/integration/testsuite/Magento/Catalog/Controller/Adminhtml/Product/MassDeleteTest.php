<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for mass product deleting.
 *
 * @see \Magento\Catalog\Controller\Adminhtml\Product\MassDelete
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class MassDeleteTest extends AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     *
     * @return void
     */
    public function testDeleteSimpleProductViaMassAction(): void
    {
        $productIds = [10, 11, 12];
        $this->dispatchMassDeleteAction($productIds);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('A total of %1 record(s) have been deleted.', count($productIds))]),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('backend/catalog/product/index'));
    }

    /**
     * @return void
     */
    public function testDeleteNotExistingProductViaMassAction(): void
    {
        $this->dispatchMassDeleteAction([989]);
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_ERROR);
        $this->assertRedirect($this->stringContains('backend/catalog/product/index'));
    }

    /**
     * Dispatch mass delete action.
     *
     * @param array $productIds
     * @return void
     */
    private function dispatchMassDeleteAction(array $productIds): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParams(['selected' => $productIds, 'namespace' => 'product_listing']);
        $this->dispatch('backend/catalog/product/massDelete/');
    }
}
