<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Get Attribute Option Id by Attribute Id and Option Label.
 */
class GetAttributeOptionId
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $connection
     */
    public function __construct(
        ResourceConnection $connection
    ) {
        $this->resource = $connection;
    }

    /**
     * Returns Attribute Option Id by Attribute Id and Option Label.
     *
     * @param int $attributeId
     * @param string $optionLabel
     *
     * @return string
     */
    public function execute(int $attributeId, string $optionLabel): string
    {
        $connection = $this->resource->getConnection();
        $optionTable = $this->resource->getTableName('eav_attribute_option');
        $optionValueTable = $this->resource->getTableName('eav_attribute_option_value');
        $select = $connection->select()
            ->from(
                ['ovt' => $optionValueTable],
                'option_id'
            )->joinInner(
                ['ot' => $optionTable],
                'ovt.option_id = ot.option_id',
                []
            )->where(
                'ovt.value = ?',
                $optionLabel
            )->where(
                'ot.attribute_id = ?',
                $attributeId
            );

        return $connection->fetchOne($select);
    }
}
