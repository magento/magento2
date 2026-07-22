<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeManagementInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;

/**
 * Product attribute fixture
 *
 * Usage examples:
 *
 * 1. Create an attribute with default data
 * <pre>
 *  #[
 *      DataFixture(AttributeFixture::class, as: 'attribute')
 *  ]
 * </pre>
 * 2. Create an attribute with custom data
 * <pre>
 *  #[
 *      DataFixture(AttributeFixture::class, ['is_filterable' => true], 'attribute')
 *  ]
 * </pre>
 */
class Attribute implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        ProductAttributeInterface::ATTRIBUTE_ID => null,
        ProductAttributeInterface::ATTRIBUTE_CODE => 'product_attribute%uniqid%',
        ProductAttributeInterface::ENTITY_TYPE_ID => '4',
        ProductAttributeInterface::SCOPE => ProductAttributeInterface::SCOPE_GLOBAL_TEXT,
        ProductAttributeInterface::IS_USER_DEFINED => true,
        ProductAttributeInterface::IS_SEARCHABLE => false,
        ProductAttributeInterface::IS_FILTERABLE => false,
        ProductAttributeInterface::IS_FILTERABLE_IN_SEARCH => false,
        ProductAttributeInterface::IS_FILTERABLE_IN_GRID => true,
        ProductAttributeInterface::IS_VISIBLE => true,
        ProductAttributeInterface::IS_VISIBLE_IN_GRID => true,
        ProductAttributeInterface::IS_VISIBLE_IN_ADVANCED_SEARCH => false,
        ProductAttributeInterface::IS_VISIBLE_ON_FRONT => false,
        ProductAttributeInterface::IS_USED_IN_GRID => true,
        ProductAttributeInterface::IS_COMPARABLE => false,
        ProductAttributeInterface::IS_USED_FOR_PROMO_RULES => false,
        ProductAttributeInterface::IS_REQUIRED => false,
        ProductAttributeInterface::IS_UNIQUE => false,
        ProductAttributeInterface::IS_WYSIWYG_ENABLED => false,
        ProductAttributeInterface::IS_HTML_ALLOWED_ON_FRONT => true,
        ProductAttributeInterface::USED_IN_PRODUCT_LISTING => false,
        ProductAttributeInterface::USED_FOR_SORT_BY => false,
        ProductAttributeInterface::POSITION => 0,
        ProductAttributeInterface::APPLY_TO => [],
        ProductAttributeInterface::OPTIONS => [],
        ProductAttributeInterface::NOTE => null,
        ProductAttributeInterface::BACKEND_TYPE => 'varchar',
        ProductAttributeInterface::BACKEND_MODEL => null,
        ProductAttributeInterface::FRONTEND_INPUT => 'text',
        ProductAttributeInterface::FRONTEND_CLASS => null,
        ProductAttributeInterface::SOURCE_MODEL => null,
        ProductAttributeInterface::EXTENSION_ATTRIBUTES_KEY => [],
        ProductAttributeInterface::CUSTOM_ATTRIBUTES => [],
        ProductAttributeInterface::FRONTEND_LABELS => [],
        'default_frontend_label' => 'Product Attribute%uniqid%',
        'validation_rules' => [],
        "default_value" => null,
    ];

    private const DEFAULT_ATTRIBUTE_SET_DATA = [
        '_set_id' => null,
        '_group_id' => null,
        '_sort_order' => 0,
    ];

    /**
     * @param ServiceFactory $serviceFactory
     * @param ProcessorInterface $dataProcessor
     * @param EavSetup $eavSetup
     * @param ProductAttributeManagementInterface $productAttributeManagement
     * @param ProductAttributeInterfaceFactory $attributeFactory
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param DataMerger $dataMerger
     */
    public function __construct(
        private readonly ServiceFactory $serviceFactory,
        private readonly ProcessorInterface $dataProcessor,
        private readonly EavSetup $eavSetup,
        private readonly ProductAttributeManagementInterface $productAttributeManagement,
        private readonly ProductAttributeInterfaceFactory $attributeFactory,
        private readonly ProductAttributeRepositoryInterface $productAttributeRepository,
        private readonly DataObjectHelper $dataObjectHelper,
        private readonly DataMerger $dataMerger
    ) {
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Attribute::DEFAULT_DATA.
     *
     * Additional fields:
     *  - `_set_id`: int - attribute set ID to assign the attribute to
     *  - `_group_id`: int - attribute group ID to assign the attribute to
     *  - `_sort_order`: int - sort order within the attribute group
     *
     * @return DataObject|null
     */
    public function apply(array $data = []): ?DataObject
    {
        $attributeData = array_diff_key($data, self::DEFAULT_ATTRIBUTE_SET_DATA);
        $attributeSetData = $this->prepareAttributeSetData(
            array_intersect_key($data, self::DEFAULT_ATTRIBUTE_SET_DATA)
        );
        
        $attribute = $this->attributeFactory->create();
        $attributeData = $this->prepareData($attributeData);

        $this->dataObjectHelper->populateWithArray(
            $attribute,
            $attributeData,
            ProductAttributeInterface::class
        );
        // Add data that are not part of the interface
        $attribute->addData(array_diff_key($attributeData, self::DEFAULT_DATA));
        if (isset($attributeData['scope'])) {
            $attribute->setScope($attributeData['scope']);
        }
        $attribute = $this->productAttributeRepository->save($attribute);

        $this->productAttributeManagement->assign(
            $attributeSetData['_set_id'],
            $attributeSetData['_group_id'],
            $attribute->getAttributeCode(),
            $attributeSetData['_sort_order']
        );

        return $attribute;
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $service = $this->serviceFactory->create(ProductAttributeRepositoryInterface::class, 'deleteById');
        $service->execute(
            [
                'attributeCode' => $data->getAttributeCode()
            ]
        );
    }

    /**
     * Prepare attribute data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = $this->dataMerger->merge(self::DEFAULT_DATA, $data, false);
        $data['frontend_label'] ??= $data['default_frontend_label'];

        return $this->dataProcessor->process($this, $data);
    }

    /**
     * Prepare attribute set data
     *
     * @param array $data
     * @return array
     */
    private function prepareAttributeSetData(array $data): array
    {
        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');
        $attributeGroupId = $this->eavSetup->getDefaultAttributeGroupId(Product::ENTITY, $attributeSetId);
        $attributeSetData = [
            '_set_id' => $attributeSetId,
            '_group_id' => $attributeGroupId,
        ];
        $data = array_merge(self::DEFAULT_ATTRIBUTE_SET_DATA, $attributeSetData, $data);

        return array_intersect_key($data, self::DEFAULT_ATTRIBUTE_SET_DATA);
    }
}
