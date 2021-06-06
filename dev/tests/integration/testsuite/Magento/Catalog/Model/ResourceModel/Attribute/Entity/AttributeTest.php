<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Attribute\Entity;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;

/**
 * Test Eav Resource Entity Attribute functionality
 *
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/multiselect_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
 * @magentoDbIsolation enabled
 */
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var AttributeResource
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->attributeRepository = $this->objectManager->get(AttributeRepository::class);
        $this->model = $this->objectManager->get(Attribute::class);
    }

    /**
     * Test to Clear selected option in entities after remove
     */
    public function testClearSelectedOptionInEntities()
    {
        $dropdownAttribute = $this->loadAttribute('dropdown_attribute');
        $dropdownOption = array_keys($dropdownAttribute->getOptions())[1];

        $multiplyAttribute = $this->loadAttribute('multiselect_attribute');
        $multiplyOptions = array_keys($multiplyAttribute->getOptions());
        $multiplySelectedOptions = implode(',', $multiplyOptions);
        $multiplyOptionToRemove = $multiplyOptions[1];
        unset($multiplyOptions[1]);
        $multiplyOptionsExpected = implode(',', $multiplyOptions);

        $product = $this->loadProduct('simple');
        $product->setData('dropdown_attribute', $dropdownOption);
        $product->setData('multiselect_attribute', $multiplySelectedOptions);
        $this->productRepository->save($product);

        $product = $this->loadProduct('simple');
        $this->assertEquals(
            $dropdownOption,
            $product->getData('dropdown_attribute'),
            'The dropdown attribute is not selected'
        );
        $this->assertEquals(
            $multiplySelectedOptions,
            $product->getData('multiselect_attribute'),
            'The multiselect attribute is not selected'
        );

        $this->removeAttributeOption($dropdownAttribute, $dropdownOption);
        $this->removeAttributeOption($multiplyAttribute, $multiplyOptionToRemove);

        $product = $this->loadProduct('simple');
        $this->assertEmpty($product->getData('dropdown_attribute'));
        $this->assertEquals($multiplyOptionsExpected, $product->getData('multiselect_attribute'));
    }

    /**
     * Remove option from attribute
     *
     * @param Attribute $attribute
     * @param int $optionId
     */
    private function removeAttributeOption(Attribute $attribute, int $optionId): void
    {
        $removalMarker = [
            'option' => [
                'value' => [$optionId => []],
                'delete' => [$optionId => '1'],
            ],
        ];
        $attribute->addData($removalMarker);
        $attribute->save($attribute);
    }

    /**
     * Load product by sku
     *
     * @param string $sku
     * @return Product
     */
    private function loadProduct(string $sku): Product
    {
        return $this->productRepository->get($sku, true, null, true);
    }

    /**
     * Load attrubute by code
     *
     * @param string $attributeCode
     * @return Attribute
     */
    private function loadAttribute(string $attributeCode): Attribute
    {
        /** @var Attribute $attribute */
        $attribute = $this->objectManager->create(Attribute::class);
        $attribute->loadByCode(4, $attributeCode);

        return $attribute;
    }
}
