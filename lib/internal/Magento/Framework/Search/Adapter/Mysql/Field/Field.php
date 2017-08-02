<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Adapter\Mysql\Field;

/**
 * Class \Magento\Framework\Search\Adapter\Mysql\Field\Field
 *
 * @since 2.0.0
 */
class Field implements FieldInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    private $column;

    /**
     * @var int|null
     * @since 2.0.0
     */
    private $attributeId;

    /**
     * @var int
     * @since 2.0.0
     */
    private $type;

    /**
     * @param string $column
     * @param int|null $attributeId
     * @param int $type
     * @since 2.0.0
     */
    public function __construct($column, $attributeId = null, $type = self::TYPE_FULLTEXT)
    {
        $this->column = $column;
        $this->attributeId = $attributeId;
        $this->type = $type;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return int|null
     * @since 2.0.0
     */
    public function getAttributeId()
    {
        return $this->attributeId;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getType()
    {
        return $this->type;
    }
}
