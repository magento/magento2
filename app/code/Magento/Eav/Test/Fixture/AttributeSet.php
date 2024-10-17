<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Fixture;

use Magento\Eav\Api\AttributeSetManagementInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;

class AttributeSet implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'attribute_set_id' => null,
        'attribute_set_name' => 'attribute_set%uniqid%',
        'sort_order' => 0,
        'entity_type_code' => null,
        'skeleton_id' => null,
    ];

    /**
     * @param ServiceFactory $serviceFactory
     * @param ProcessorInterface $dataProcessor
     */
    public function __construct(
        private readonly ServiceFactory $serviceFactory,
        private readonly ProcessorInterface $dataProcessor
    ) {
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as AttributeSet::DEFAULT_DATA.
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $skeletonId = $data['skeleton_id'];
        $entityTypeCode = $data['entity_type_code'];
        unset($data['skeleton_id'], $data['entity_type_code']);
        $service = $this->serviceFactory->create(AttributeSetManagementInterface::class, 'create');

        return $service->execute(
            [
                'attributeSet' => $this->dataProcessor->process($this, $data),
                'entityTypeCode' => $entityTypeCode,
                'skeletonId' => $skeletonId,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $service = $this->serviceFactory->create(AttributeSetRepositoryInterface::class, 'deleteById');
        $service->execute(
            [
                'attributeSetId' => $data->getAttributeSetId()
            ]
        );
    }
}
