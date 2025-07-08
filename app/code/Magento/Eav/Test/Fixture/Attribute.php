<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Fixture;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;

class Attribute implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'entity_type_id' => null,
        'attribute_id' => null,
        'attribute_code' => 'attribute%uniqid%',
        'default_frontend_label' => 'Attribute%uniqid%',
        'frontend_labels' => [],
        'frontend_input' => 'text',
        'backend_type' => 'varchar',
        'is_required' => false,
        'is_user_defined' => true,
        'note' => null,
        'backend_model' => null,
        'source_model' => null,
        'default_value' => null,
        'is_unique' => '0',
        'frontend_class' => null
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
        if (empty($data['entity_type_id'])) {
            throw new InvalidArgumentException(
                __(
                    '"%field" value is required to create an attribute',
                    [
                        'field' => 'entity_type_id'
                    ]
                )
            );
        }

        $mergedData = $this->processor->process($this, $this->dataMerger->merge(self::DEFAULT_DATA, $data));

        $this->serviceFactory->create(AttributeRepositoryInterface::class, 'save')->execute(
            [
                'attribute' => $mergedData
            ]
        );

        return $this->attributeRepository->get($mergedData['entity_type_id'], $mergedData['attribute_code']);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $this->attributeRepository->deleteById($data['attribute_id']);
    }
}
