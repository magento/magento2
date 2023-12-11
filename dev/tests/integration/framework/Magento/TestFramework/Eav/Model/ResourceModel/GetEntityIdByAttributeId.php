<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Eav\Model\ResourceModel;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set as AttributeSetResource;

/**
 * Search and return attribute data from eav entity attribute table.
 */
class GetEntityIdByAttributeId
{
    /**
     * @var AttributeSetResource
     */
    private $attributeSetResource;

    /**
     * @param AttributeSetResource $setResource
     */
    public function __construct(
        AttributeSetResource $setResource
    ) {
        $this->attributeSetResource = $setResource;
    }

    /**
     * Returns entity attribute by id.
     *
     * @param int $setId
     * @param int $attributeId
     * @param int|null $attributeGroupId
     * @return int|null
     */
    public function execute(int $setId, int $attributeId, ?int $attributeGroupId = null): ?int
    {
        $select = $this->attributeSetResource->getConnection()->select()
            ->from(
                $this->attributeSetResource->getTable('eav_entity_attribute'),
                'entity_attribute_id'
            )
            ->where('attribute_set_id = ?', $setId)
            ->where('attribute_id = ?', $attributeId);

        if ($attributeGroupId !== null) {
            $select->where('attribute_group_id = ?', $attributeGroupId);
        }
        $entityAttributeId = $this->attributeSetResource->getConnection()->fetchOne($select);

        return $entityAttributeId ? (int)$entityAttributeId : null;
    }
}
