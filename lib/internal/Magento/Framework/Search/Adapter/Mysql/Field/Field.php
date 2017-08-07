<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Adapter\Mysql\Field;

/**
 * Class \Magento\Framework\Search\Adapter\Mysql\Field\Field
 *
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
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return int|null
     */
    public function getAttributeId()
    {
        return $this->attributeId;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
}
