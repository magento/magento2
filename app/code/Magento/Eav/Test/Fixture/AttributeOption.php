<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Fixture;

use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
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
        'is_default' => ''
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
            $this->processor->process($this, $this->dataMerger->merge(self::DEFAULT_DATA, $data))
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
            if ($option->getLabel() === $mergedData['label']) {
                return $option;
            }
        }

        return null;
    }
}
