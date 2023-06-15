<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Plugin\Catalog\ResourceModel\Attribute;

use Magento\Catalog\Model\ResourceModel\Attribute\RemoveProductAttributeData;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\AbstractModel;

/**
 * Plugin for deleting wee tax attributes data on unassigning weee attribute from attribute set.
 */
class RemoveProductWeeData
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Deletes wee tax attributes data on unassigning weee attribute from attribute set.
     *
     * @param RemoveProductAttributeData $subject
     * @param \Closure $proceed
     * @param AbstractModel $object
     * @param int $attributeSetId
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRemoveData(
        RemoveProductAttributeData $subject,
        \Closure $proceed,
        AbstractModel $object,
        int $attributeSetId
    ) {
        if ($object->getFrontendInput() == 'weee') {
            $select =$this->resourceConnection->getConnection()->select()
                ->from(['b' => $this->resourceConnection->getTableName('weee_tax')])
                ->join(
                    ['e' => $object->getEntity()->getEntityTable()],
                    'b.entity_id = e.entity_id'
                )->where('b.attribute_id = ?', $object->getAttributeId())
                ->where('e.attribute_set_id = ?', $attributeSetId);

            $this->resourceConnection->getConnection()->query($select->deleteFromSelect('b'));
        } else {
            $proceed($object, $attributeSetId);
        }
    }
}
