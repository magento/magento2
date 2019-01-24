<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Adapter\Mysql\Field;

/**
 * @inheritdoc
 * @deprecated
 * @see \Magento\ElasticSearch
 */
class Field implements FieldInterface
{
    /**
     * @var string
     */
    private $column;

    /**
     * @var int|null
     */
    private $attributeId;

    /**
     * @var int
     */
    private $type;

    /**
     * @param string $column
     * @param int|null $attributeId
     * @param int $type
     */
    public function __construct($column, $attributeId = null, $type = self::TYPE_FULLTEXT)
    {
        $this->column = $column;
        $this->attributeId = $attributeId;
        $this->type = $type;
    }

    /**
     * Get column.
     *
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Get attribute ID.
     *
     * @return int|null
     */
    public function getAttributeId()
    {
        return $this->attributeId;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
}
