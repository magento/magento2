<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Api\ProductAttributeManagementInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Attribute as ResourceModelAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Eav\Model\AttributeFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;

class Attribute implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'is_wysiwyg_enabled' => false,
        'is_html_allowed_on_front' => true,
        'used_for_sort_by' => false,
        'is_filterable' => false,
        'is_filterable_in_search' => false,
        'is_used_in_grid' => true,
        'is_visible_in_grid' => true,
        'is_filterable_in_grid' => true,
        'position' => 0,
        'apply_to' => [],
        'is_searchable' => false,
        'is_visible_in_advanced_search' => false,
        'is_comparable' => false,
        'is_used_for_promo_rules' => false,
        'is_visible_on_front' => false,
        'used_in_product_listing' => false,
        'is_visible' => true,
        'scope' => 'store',
        'attribute_code' => 'product_attribute%uniqid%',
        'frontend_input' => 'text',
        'entity_type_id' => '4',
        'is_required' => false,
        'options' => [],
        'is_user_defined' => true,
        'default_frontend_label' => 'Product Attribute%uniqid%',
        'frontend_labels' => [],
        'backend_type' => 'varchar',
        'is_unique' => '0',
        'validation_rules' => []
    ];

    private const DEFAULT_ATTRIBUTE_SET_DATA = [
        '_set_id' => null,
        '_group_id' => null,
        '_sort_order' => 0,
    ];

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var ProductAttributeManagementInterface
     */
    private $productAttributeManagement;

    /**
     * @var AttributeFactory
     */
    private AttributeFactory $attributeFactory;

    /**
     * @var DataMerger
     */
    private DataMerger $dataMerger;

    /**
     * @var ResourceModelAttribute
     */
    private ResourceModelAttribute $resourceModelAttribute;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private ProductAttributeRepositoryInterface $productAttributeRepository;

    /**
     * @param ServiceFactory $serviceFactory
     * @param ProcessorInterface $dataProcessor
     * @param EavSetup $eavSetup
     * @param ProductAttributeManagementInterface $productAttributeManagement
     * @param AttributeFactory $attributeFactory
     * @param DataMerger $dataMerger
     * @param ResourceModelAttribute $resourceModelAttribute
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        ProcessorInterface $dataProcessor,
        EavSetup $eavSetup,
        ProductAttributeManagementInterface $productAttributeManagement,
        AttributeFactory $attributeFactory,
        DataMerger $dataMerger,
        ResourceModelAttribute $resourceModelAttribute,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->dataProcessor = $dataProcessor;
        $this->eavSetup = $eavSetup;
        $this->productAttributeManagement = $productAttributeManagement;
        $this->attributeFactory = $attributeFactory;
        $this->dataMerger = $dataMerger;
        $this->resourceModelAttribute = $resourceModelAttribute;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Attribute::DEFAULT_DATA.
     * @return DataObject|null
     */
    public function apply(array $data = []): ?DataObject
    {
        if (array_key_exists('additional_data', $data)) {
            return $this->applyAttributeWithAdditionalData($data);
        }

        $attribute = $this->attributeFactory->createAttribute(
            EavAttribute::class,
            $this->prepareData(array_diff_key($data, self::DEFAULT_ATTRIBUTE_SET_DATA))
        );
        $attribute = $this->productAttributeRepository->save($attribute);

        $attributeSetData = $this->prepareAttributeSetData(
            array_intersect_key($data, self::DEFAULT_ATTRIBUTE_SET_DATA)
        );

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
     * @param array $data Parameters. Same format as Attribute::DEFAULT_DATA.
     * @return DataObject|null
     */
    private function applyAttributeWithAdditionalData(array $data = []): ?DataObject
    {
        $defaultData = array_merge(self::DEFAULT_DATA, ['additional_data' => null]);
        /** @var EavAttribute $attr */
        $attr = $this->attributeFactory->createAttribute(EavAttribute::class, $defaultData);
        $mergedData = $this->dataProcessor->process($this, $this->dataMerger->merge($defaultData, $data));

        $attributeSetData = $this->prepareAttributeSetData(
            array_intersect_key($data, self::DEFAULT_ATTRIBUTE_SET_DATA)
        );

        $attr->setData(array_merge($mergedData, $attributeSetData));
        $this->resourceModelAttribute->save($attr);
        return $attr;
    }

    /**
     * Prepare attribute data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
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
