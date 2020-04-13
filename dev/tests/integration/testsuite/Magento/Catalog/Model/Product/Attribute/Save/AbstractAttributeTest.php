<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Save;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Exception;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Base class for text product attributes
 */
abstract class AbstractAttributeTest extends TestCase
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var ProductAttributeInterface */
    protected $attribute;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->attributeRepository = $this->objectManager->create(AttributeRepositoryInterface::class);
    }

    /**
     * @dataProvider productProvider
     * @param $productSku
     * @return void
     */
    public function testSaveAttribute(string $productSku): void
    {
        $product = $this->setAttributeValueAndValidate($productSku, $this->getDefaultAttributeValue());
        $product = $this->productRepository->save($product);
        $this->assertEquals($this->getDefaultAttributeValue(), $product->getData($this->getAttributeCode()));
    }

    /**
     * @dataProvider productProvider
     * @param string $productSku
     * @return void
     */
    public function testRequiredAttribute(string $productSku): void
    {
        $this->expectException(Exception::class);
        $messageFormat = 'The "%s" attribute value is empty. Set the attribute and try again.';
        $this->expectExceptionMessage(
            (string)__(sprintf($messageFormat, $this->getAttribute()->getDefaultFrontendLabel()))
        );
        $this->prepareAttribute(['is_required' => true]);
        $this->unsetAttributeValueAndValidate($productSku);
    }

    /**
     * @dataProvider productProvider
     * @param string $productSku
     * @return void
     */
    public function testDefaultValue(string $productSku): void
    {
        $this->prepareAttribute(['default_value' => $this->getDefaultAttributeValue()]);
        $product = $this->unsetAttributeValueAndValidate($productSku);
        $product = $this->productRepository->save($product);
        $this->assertEquals($this->getDefaultAttributeValue(), $product->getData($this->getAttributeCode()));
    }

    /**
     * @dataProvider uniqueAttributeValueProvider
     * @param string $firstSku
     * @param string $secondSku
     * @return void
     */
    public function testUniqueAttribute(string $firstSku, string $secondSku): void
    {
        $this->expectException(Exception::class);
        $messageFormat = 'The value of the "%s" attribute isn\'t unique. Set a unique value and try again.';
        $this->expectExceptionMessage(
            (string)__(sprintf($messageFormat, $this->getAttribute()->getDefaultFrontendLabel()))
        );
        $this->prepareAttribute(['is_unique' => 1]);
        $product = $this->setAttributeValueAndValidate($firstSku, $this->getDefaultAttributeValue());
        $this->productRepository->save($product);
        $this->setAttributeValueAndValidate($secondSku, $this->getDefaultAttributeValue());
    }

    /**
     * Get attribute
     *
     * @return ProductAttributeInterface
     */
    protected function getAttribute(): ProductAttributeInterface
    {
        if ($this->attribute === null) {
            $this->attribute = $this->attributeRepository->get(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $this->getAttributeCode()
            );
        }

        return $this->attribute;
    }

    /**
     * Set attribute value to product and validate the product
     *
     * @param string $attributeValue
     * @param string $productSku
     * @return ProductInterface
     */
    protected function setAttributeValueAndValidate(string $productSku, string $attributeValue): ProductInterface
    {
        $product = $this->productRepository->get($productSku);
        $product->addData([$this->getAttributeCode() => $attributeValue]);
        $product->validate();

        return $product;
    }

    /**
     * Unset attribute value of the product and validate the product
     *
     * @param string $productSku
     * @return ProductInterface
     */
    private function unsetAttributeValueAndValidate(string $productSku): ProductInterface
    {
        $product = $this->productRepository->get($productSku);
        $product->unsetData($this->getAttributeCode());
        $product->validate();

        return $product;
    }

    /**
     * Prepare attribute to test
     *
     * @param array $data
     * @return void
     */
    private function prepareAttribute(array $data): void
    {
        $attribute = $this->getAttribute();
        $attribute->addData($data);
        $this->attributeRepository->save($attribute);
    }

    /**
     * Returns attribute code for current test
     *
     * @return string
     */
    abstract protected function getAttributeCode(): string;

    /**
     * Get default value for current attribute
     *
     * @return string
     */
    abstract protected function getDefaultAttributeValue(): string;

    /**
     * Products provider for tests
     *
     * @return array
     */
    abstract public function productProvider(): array;

    /**
     * Provider for unique attribute tests
     *
     * @return array
     */
    abstract public function uniqueAttributeValueProvider(): array;
}
