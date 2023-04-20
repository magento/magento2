<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Fixture;

use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\ResourceModel\Attribute as ResourceModelAttribute;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\AttributeFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class CustomerAttribute implements RevertibleDataFixtureInterface
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
        'frontend_class' => null,
        'used_in_forms' => [],
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
