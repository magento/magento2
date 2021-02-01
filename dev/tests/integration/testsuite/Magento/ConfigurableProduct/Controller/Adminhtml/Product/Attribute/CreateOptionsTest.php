<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for creates options for product attributes.
 *
 * @see \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute\CreateOptions
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class CreateOptionsTest extends AbstractBackendController
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var SerializerInterface */
    private $json;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->json = $this->_objectManager->get(SerializerInterface::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     *
     * @return void
     */
    public function testAddOptionWithUniqueValidationOneMoreTime(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParams([
            'options' => [0 => [
                    'label' => 'Option 1',
                    'is_new' => true,
                    'attribute_id' => $this->getFirstAttributeId()
                    ]
                ]
            ]);
        $this->dispatch('backend/catalog/product_attribute/createOptions');
        $responseBody = $this->json->unserialize($this->getResponse()->getBody());
        $this->assertNotEmpty($responseBody['message']);
        $this->assertStringContainsString(
            (string)__('The value of attribute ""test_configurable"" must be unique'),
            $responseBody['message']
        );
    }

    /**
     * Get first attribute id
     *
     * @return int
     */
    private function getFirstAttributeId(): int
    {
        $configurableProduct = $this->productRepository->get('configurable');
        $options = $configurableProduct->getExtensionAttributes()->getConfigurableProductOptions();
        foreach ($options as $option) {
            $attributeIds[] = $option->getAttributeId();
        }

        return (int)array_shift($attributeIds);
    }
}
