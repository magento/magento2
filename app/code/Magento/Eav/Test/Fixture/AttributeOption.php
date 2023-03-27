<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Fixture;

use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\DataFixtureInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;

class AttributeOption implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'entity_type' => null,
        'attribute_code' => null,
        'label' => 'Option Label %uniqid%',
        'sort_order' => null,
        'store_labels' => '',
        'is_default' => false
    ];

    /**
     * @var ServiceFactory
     */
    private ServiceFactory $serviceFactory;

    /**
     * @var DataMerger
     */
    private DataMerger $dataMerger;

    /**
     * @var ProcessorInterface
     */
    private ProcessorInterface $processor;

    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @param ServiceFactory $serviceFactory
     * @param DataMerger $dataMerger
     * @param ProcessorInterface $processor
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        DataMerger $dataMerger,
        ProcessorInterface $processor,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->dataMerger = $dataMerger;
        $this->processor = $processor;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        if (empty($data['entity_type'])) {
            throw new InvalidArgumentException(
                __(
                    '"%field" value is required to create an attribute option',
                    [
                        'field' => 'entity_type_id'
                    ]
                )
            );
        }

        if (empty($data['attribute_code'])) {
            throw new InvalidArgumentException(
                __(
                    '"%field" value is required to create an attribute option',
                    [
                        'field' => 'attribute_code'
                    ]
                )
            );
        }

        $mergedData = array_filter(
            $this->processor->process($this, $this->dataMerger->merge(self::DEFAULT_DATA, $data)),
            function ($value) {
                return $value !== null;
            }
        );

        $entityType = $mergedData['entity_type'];
        $attributeCode = $mergedData['attribute_code'];
        unset($mergedData['entity_type'], $mergedData['attribute_code']);

        $this->serviceFactory->create(AttributeOptionManagementInterface::class, 'add')->execute(
            [
                'entityType' => $entityType,
                'attributeCode' => $attributeCode,
                'option' => $mergedData
            ]
        );

        $attribute = $this->attributeRepository->get($entityType, $attributeCode);

        foreach ($attribute->getOptions() as $option) {
            if ($this->getDefaultLabel($mergedData) === $option->getLabel()) {
                if (isset($mergedData['is_default']) && $mergedData['is_default']) {
                    $option->setIsDefault(true);
                }
                return $option;
            }
        }

        return null;
    }

    /**
     * Retrieve default label or label for default store
     *
     * @param array $mergedData
     * @return string
     */
    private function getDefaultLabel(array $mergedData): string
    {
        $defaultLabel = $mergedData['label'];
        if (!isset($mergedData['store_labels']) || !is_array($mergedData['store_labels'])) {
            return $defaultLabel;
        }

        foreach ($mergedData['store_labels'] as $label) {
            if (isset($label['store_id']) && $label['store_id'] === 0 && isset($label['label'])) {
                return $label['label'];
            }
        }

        return $defaultLabel;
    }
}
