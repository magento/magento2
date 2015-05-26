<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Adapter\Mysql\Field;

class Field implements FieldInterface
{
    /**
     * @var string
     */
    private $field;
    /**
     * @var int|null
     */
    private $attributeId;
    /**
     * @var int
     */
    private $type;

    /**
     * @param string $field
     * @param int|null $attributeId
     * @param int $type
     */
    public function __construct($field, $attributeId = null, $type = self::TYPE_FULLTEXT)
    {
        $this->field = $field;
        $this->attributeId = $attributeId;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getField();
    }
}
