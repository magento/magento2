<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Save;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Checks product saving with deleted category before reindex is done
 */
class DeleteCategoryTest extends AbstractBackendController
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     */
    public function testDeleteCustomOptionWithTypeField(): void
    {
        $category = Bootstrap::getObjectManager()->create(Category::class);
        $category->load(333);
        $category->delete();
        $product = $this->productRepository->get('simple333');
        $this->productRepository->save($product);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product/save/id/' . $product->getEntityId());
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the product.')]),
            MessageInterface::TYPE_SUCCESS
        );
    }
}
