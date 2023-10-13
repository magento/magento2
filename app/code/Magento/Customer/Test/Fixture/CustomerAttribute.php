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
     * @var CustomerAttributeDefaultData
     */
    private CustomerAttributeDefaultData $customerAttributeDefaultData;

    /**
     * @param DataMerger $dataMerger
     * @param ProcessorInterface $processor
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeFactory $attributeFactory
     * @param ResourceModelAttribute $resourceModelAttribute
     * @param CustomerAttributeDefaultData $customerAttributeDefaultData
     */
    public function __construct(
        DataMerger $dataMerger,
        ProcessorInterface $processor,
        AttributeRepositoryInterface $attributeRepository,
        AttributeFactory $attributeFactory,
        ResourceModelAttribute $resourceModelAttribute,
        CustomerAttributeDefaultData $customerAttributeDefaultData
    ) {
        $this->dataMerger = $dataMerger;
        $this->processor = $processor;
        $this->attributeFactory = $attributeFactory;
        $this->resourceModelAttribute = $resourceModelAttribute;
        $this->attributeRepository = $attributeRepository;
        $this->customerAttributeDefaultData = $customerAttributeDefaultData;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $defaultData = $this->customerAttributeDefaultData->getData();
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
        $attr = $this->attributeFactory->createAttribute(Attribute::class, $defaultData);
        $mergedData = $this->processor->process($this, $this->dataMerger->merge($defaultData, $data));
        $attr->setData($mergedData);
        if (isset($data['website_id'])) {
            $attr->setWebsite($data['website_id']);
        }
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
