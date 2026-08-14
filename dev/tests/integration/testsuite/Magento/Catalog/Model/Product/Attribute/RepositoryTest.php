<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Validator\Attribute\Code;
use Magento\Framework\Exception\InputException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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
        $this->clearAttributeCodeValidatorMessages();
    }

    /**
     * Shared attribute-code validator keeps messages between calls.
     */
    private function clearAttributeCodeValidatorMessages(): void
    {
        $validator = $this->objectManager->get(Code::class);
        $method = new \ReflectionMethod($validator, '_clearMessages');
        $method->invoke($validator);
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
     * @param string $fieldName
     * @param string $fieldValue
     * @return void
     */
    #[DataProvider('errorProvider')]
    public function testSaveWithInvalidCode(string $fieldName, string $fieldValue): void
    {
        $this->expectExceptionObject(InputException::invalidFieldValue($fieldName, $fieldValue));
        $this->createdAttribute = $this->saveAttributeWithData($this->hydrateData([$fieldName => $fieldValue]));
    }

    /**
     * @return array
     */
    public static function errorProvider(): array
    {
        return [
            'with_invalid_attribute_code' => [
                'fieldName' => 'attribute_code',
                'fieldValue' => '****',
            ],
            'with_invalid_frontend_input' => [
                'fieldName' => 'frontend_input',
                'fieldValue' => 'invalid_input',
            ],
        ];
    }

    /**
     * @param string $frontendInput
     * @param string $field
     * @param int $value
     * @return void
     */
    #[DataProvider('invalidFilterableInputTypeProvider')]
    public function testSaveRejectsFilterableForUnsupportedInputType(
        string $frontendInput,
        string $field,
        int $value
    ): void {
        $this->expectExceptionObject(InputException::invalidFieldValue($field, $value));
        $this->createdAttribute = $this->saveAttributeWithData(
            $this->hydrateData(
                [
                    'attribute_code' => 'rej_' . $frontendInput . '_' . $field,
                    'frontend_input' => $frontendInput,
                    'frontend_label' => 'Rejected ' . $frontendInput,
                    $field => $value,
                ]
            )
        );
    }

    /**
     * @return array
     */
    public static function invalidFilterableInputTypeProvider(): array
    {
        return [
            'text_is_filterable' => ['text', ProductAttributeInterface::IS_FILTERABLE, 1],
            'textarea_is_filterable' => ['textarea', ProductAttributeInterface::IS_FILTERABLE, 1],
            'date_is_filterable' => ['date', ProductAttributeInterface::IS_FILTERABLE, 1],
            'datetime_is_filterable' => ['datetime', ProductAttributeInterface::IS_FILTERABLE, 1],
            'media_image_is_filterable' => ['media_image', ProductAttributeInterface::IS_FILTERABLE, 1],
            'text_is_filterable_in_search' => ['text', ProductAttributeInterface::IS_FILTERABLE_IN_SEARCH, 1],
        ];
    }

    /**
     * @return void
     */
    public function testSaveAllowsFilterableForSupportedInputType(): void
    {
        $this->createdAttribute = $this->saveAttributeWithData(
            $this->hydrateData(
                [
                    'attribute_code' => 'repo_filt_bool',
                    'frontend_input' => 'boolean',
                    'frontend_label' => 'Allowed Filterable Boolean',
                    ProductAttributeInterface::IS_FILTERABLE => 1,
                ]
            )
        );

        $this->assertSame(1, (int)$this->createdAttribute->getIsFilterable());
    }

    /**
     * @return void
     */
    public function testSaveRejectsEnablingFilterableOnExistingTextAttribute(): void
    {
        $this->createdAttribute = $this->saveAttributeWithData(
            $this->hydrateData(
                [
                    'attribute_code' => 'repo_txt_then_filt',
                    'frontend_input' => 'text',
                    'frontend_label' => 'Text Then Filterable',
                    ProductAttributeInterface::IS_FILTERABLE => 0,
                ]
            )
        );
        $this->createdAttribute->setIsFilterable(1);

        $this->expectExceptionObject(
            InputException::invalidFieldValue(ProductAttributeInterface::IS_FILTERABLE, 1)
        );
        $this->repository->save($this->createdAttribute);
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
