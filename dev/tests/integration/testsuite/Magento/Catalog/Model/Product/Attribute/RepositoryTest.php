<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks product attribute save behaviour.
 *
 * @see \Magento\Catalog\Model\Product\Attribute\Repository
 *
 * @magentoDbIsolation enabled
 */
class RepositoryTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductAttributeRepositoryInterface */
    private $repository;

    /** @var ProductAttributeInterfaceFactory */
    private $attributeFactory;

    /** @var ProductAttributeInterface */
    private $createdAttribute;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->repository = $this->objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->attributeFactory = $this->objectManager->get(ProductAttributeInterfaceFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->createdAttribute instanceof ProductAttributeInterface) {
            $this->repository->delete($this->createdAttribute);
        }

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testSaveWithoutAttributeCode(): void
    {
        $this->createdAttribute = $this->saveAttributeWithData(
            $this->hydrateData(['frontend_label' => 'Boolean Attribute'])
        );
        $this->assertEquals('boolean_attribute', $this->createdAttribute->getAttributeCode());
    }

    /**
     * @return void
     */
    public function testSaveWithoutAttributeAndInvalidLabelCode(): void
    {
        $this->createdAttribute = $this->saveAttributeWithData($this->hydrateData(['frontend_label' => '/$&!/']));
        $this->assertStringStartsWith('attr_', $this->createdAttribute->getAttributeCode());
    }

    /**
     * @dataProvider errorProvider
     *
     * @param string $fieldName
     * @param string $fieldValue
     * @return void
     */
    public function testSaveWithInvalidCode(string $fieldName, string $fieldValue): void
    {
        $this->expectExceptionObject(InputException::invalidFieldValue($fieldName, $fieldValue));
        $this->createdAttribute = $this->saveAttributeWithData($this->hydrateData([$fieldName => $fieldValue]));
    }

    /**
     * @return array
     */
    public function errorProvider(): array
    {
        return [
            'with_invalid_attribute_code' => [
                'field_name' => 'attribute_code',
                'field_value' => '****',
            ],
            'with_invalid_frontend_input' => [
                'field_name' => 'frontend_input',
                'field_value' => 'invalid_input',
            ],
        ];
    }

    /**
     * Save product attribute with data
     *
     * @param array $data
     * @return ProductAttributeInterface
     */
    private function saveAttributeWithData(array $data): ProductAttributeInterface
    {
        $attribute = $this->attributeFactory->create();
        $attribute->addData($data);

        return $this->repository->save($attribute);
    }

    /**
     * Hydrate data
     *
     * @param array $data
     * @return array
     */
    private function hydrateData(array $data): array
    {
        $defaultData = [
            'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'frontend_input' => 'boolean',
            'frontend_label' => 'default label',
        ];

        return array_merge($defaultData, $data);
    }
}
