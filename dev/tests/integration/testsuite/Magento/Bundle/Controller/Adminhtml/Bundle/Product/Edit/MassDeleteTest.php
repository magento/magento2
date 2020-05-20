<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for mass bundle product deleting.
 *
 * @see \Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit\MassDelete
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class MassDeleteTest extends AbstractBackendController
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_checkbox_required_option.php
     *
     * @return void
     */
    public function testDeleteBundleProductViaMassAction(): void
    {
        $product = $this->productRepository->get('bundle-product-checkbox-required-option');
        $this->dispatchMassDeleteAction([$product->getId()]);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('A total of 1 record(s) have been deleted.')]),
            MessageInterface::TYPE_SUCCESS
        );
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
