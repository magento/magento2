<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Eav\Model\Config;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Checks creating attribute options process.
 *
 * @see \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute\UpdateProductAttributeTest
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class UpdateProductAttributeTest extends AbstractBackendController
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $productRepository->cleanCache();
        $this->productAttributeRepository = $this->_objectManager->create(ProductAttributeRepositoryInterface::class);
        $this->eavConfig = $this->_objectManager->create(Config::class);
    }

    /**
     * Test updating a product attribute and checking the frontend_class for the sku attribute.
     *
     * @return void
     * @throws LocalizedException
     */
    #[
        DataFixture(AttributeFixture::class, as: 'attr'),
    ]
    public function testAttributeWithBackendTypeHasSameValueInFrontendClass()
    {
        // Load the 'sku' attribute.
        /** @var ProductAttributeInterface $attribute */
        $attribute = $this->productAttributeRepository->get('sku');
        $expectedFrontEndClass = $attribute->getFrontendClass();

        // Save the attribute.
        $this->productAttributeRepository->save($attribute);

        // Check that the value of the frontend_class changed or not.
        try {
            $skuAttribute = $this->eavConfig->getAttribute('catalog_product', 'sku');
            $this->assertEquals($expectedFrontEndClass, $skuAttribute->getFrontendClass());
        } catch (LocalizedException $e) {
            $this->fail($e->getMessage());
        }
    }
}
