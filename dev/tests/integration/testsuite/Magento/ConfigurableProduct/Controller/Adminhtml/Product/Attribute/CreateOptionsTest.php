<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Checks creating attribute options process.
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

    /** @var ProductAttributeRepositoryInterface */
    private $attributeRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->json = $this->_objectManager->get(SerializerInterface::class);
        $this->attributeRepository = $this->_objectManager->get(ProductAttributeRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     *
     * @return void
     */
    public function testAddAlreadyAddedOption(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $attribute = $this->attributeRepository->get('test_configurable');
        $this->getRequest()->setParams([
            'options' => [
                [
                    'label' => 'Option 1',
                    'is_new' => true,
                    'attribute_id' => (int)$attribute->getAttributeId(),
                ],
            ],
        ]);
        $this->dispatch('backend/catalog/product_attribute/createOptions');
        $responseBody = $this->json->unserialize($this->getResponse()->getBody());
        $this->assertNotEmpty($responseBody);
        $this->assertStringContainsString(
            (string)__('The value of attribute ""%1"" must be unique', $attribute->getAttributeCode()),
            $responseBody['message']
        );
    }
}
