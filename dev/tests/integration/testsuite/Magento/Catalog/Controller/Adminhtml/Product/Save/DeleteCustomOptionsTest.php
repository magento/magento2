<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Save;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Base test cases for delete product custom option with type "field".
 * Option deleting via product controller action save.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
 */
class DeleteCustomOptionsTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $productSku = 'simple';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var ProductCustomOptionInterfaceFactory
     */
    private $optionRepositoryFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->optionRepository = $this->_objectManager->get(ProductCustomOptionRepositoryInterface::class);
        $this->optionRepositoryFactory = $this->_objectManager->get(ProductCustomOptionInterfaceFactory::class);
    }

    /**
     * Test delete custom option with type "field".
     *
     * @dataProvider \Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\Field::getDataForCreateOptions
     *
     * @param array $optionData
     * @return void
     */
    public function testDeleteCustomOptionWithTypeField(array $optionData): void
    {
        $product = $this->productRepository->get($this->productSku);
        /** @var ProductCustomOptionInterface $option */
        $option = $this->optionRepositoryFactory->create(['data' => $optionData]);
        $option->setProductSku($product->getSku());
        $product->setOptions([$option]);
        $this->productRepository->save($product);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product/save/id/' . $product->getEntityId());
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the product.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertCount(0, $this->optionRepository->getProductOptions($product));
    }
}
