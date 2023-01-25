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
     * Test delete custom option with type "field".
     *
     * @dataProvider \Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\Field::getDataForCreateOptions
     *
     * @param array $optionData
     * @return void
     */
    public function testDeleteCustomOptionWithTypeField(array $optionData): void
    {
        $productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($this->productSku);
        /** @var ProductCustomOptionInterface $option */
        $option = $this->_objectManager->get(ProductCustomOptionInterfaceFactory::class)
            ->create(['data' => $optionData]);
        $option->setProductSku($product->getSku());
        $product->setOptions([$option]);
        $productRepository->save($product);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product/save/id/' . $product->getEntityId());
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the product.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertCount(
            0,
            $this->_objectManager->get(ProductCustomOptionRepositoryInterface::class)
                ->getProductOptions($product)
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isStatic() && 0 !== strpos($property->getDeclaringClass()->getName(), 'PHPUnit')) {
                $property->setAccessible(true);
                $property->setValue($this, null);
            }
        }
    }
}
