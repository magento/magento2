<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Model\Category\Attribute;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\AttributeFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as ResourceModelAttribute;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class CategoryAttribute implements RevertibleDataFixtureInterface
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
        'is_searchable' => '0',
        'is_visible_in_advanced_search' => '0',
        'is_comparable' => '0',
        'is_used_for_promo_rules' => '0',
        'is_visible_on_front' => '0',
        'used_in_product_listing' => '0',
        'is_visible' => true,
        'scope' => 'store',
        'attribute_code' => 'category_attribute%uniqid%',
        'frontend_input' => 'text',
        'entity_type_id' => '3',
        'is_required' => false,
        'is_user_defined' => true,
        'default_frontend_label' => 'Category Attribute%uniqid%',
        'backend_type' => 'varchar',
        'is_unique' => '0',
        'apply_to' => [],
    ];

    /**
     * @var DataMerger
     */
    private DataMerger $dataMerger;

    /**
     * @var ProcessorInterface
     */
    private ProcessorInterface $processor;

    /**
     * @var AttributeFactory
     */
    private AttributeFactory $attributeFactory;

    /**
     * @var ResourceModelAttribute
     */
    private ResourceModelAttribute $resourceModelAttribute;

    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @param DataMerger $dataMerger
     * @param ProcessorInterface $processor
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeFactory $attributeFactory
     * @param ResourceModelAttribute $resourceModelAttribute
     */
    public function __construct(
        DataMerger $dataMerger,
        ProcessorInterface $processor,
        AttributeRepositoryInterface $attributeRepository,
        AttributeFactory $attributeFactory,
        ResourceModelAttribute $resourceModelAttribute
    ) {
        $this->dataMerger = $dataMerger;
        $this->processor = $processor;
        $this->attributeFactory = $attributeFactory;
        $this->resourceModelAttribute = $resourceModelAttribute;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        /** @var Attribute $attr */
        $attr = $this->attributeFactory->createAttribute(Attribute::class, self::DEFAULT_DATA);
        $mergedData = $this->processor->process($this, $this->dataMerger->merge(self::DEFAULT_DATA, $data));
        $attr->setData($mergedData);
        $this->resourceModelAttribute->save($attr);
        return $attr;
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $this->attributeRepository->deleteById($data['attribute_id']);
    }
}
