<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\DataObject;
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
        'is_searchable' => '0',
        'is_visible_in_advanced_search' => '0',
        'is_comparable' => '0',
        'is_used_for_promo_rules' => '0',
        'is_visible_on_front' => '0',
        'used_in_product_listing' => '0',
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

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @param ServiceFactory $serviceFactory
     * @param ProcessorInterface $dataProcessor
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        ProcessorInterface $dataProcessor
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Attribute::DEFAULT_DATA.
     */
    public function apply(array $data = []): ?DataObject
    {
        $service = $this->serviceFactory->create(ProductAttributeRepositoryInterface::class, 'save');

        return $service->execute(
            [
                'attribute' => $this->prepareData($data)
            ]
        );
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
        $data = array_merge(self::DEFAULT_DATA, $data);

        return $this->dataProcessor->process($this, $data);
    }
}
