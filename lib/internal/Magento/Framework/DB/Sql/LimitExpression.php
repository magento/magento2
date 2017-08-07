<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sql;

/**
 * Class LimitExpression
 * @since 2.1.0
 */
class LimitExpression extends Expression
{
    /**
     * @var string
     * @since 2.1.0
     */
    protected $sql;

    /**
     * @var int
     * @since 2.1.0
     */
    protected $count;

    /**
     * @var int
     * @since 2.1.0
     */
    protected $offset;

    /**
     * @param string $sql
     * @param int $count
     * @param int $offset
     * @since 2.1.0
     */
    public function __construct(
        $sql,
        $count,
        $offset = 0
    ) {
        $this->sql = $sql;
        $this->count = $count;
        $this->offset =  $offset;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function __toString()
    {
        $sql = $this->sql;
        $count = intval($this->count);
        if ($count <= 0) {
            /** @see Zend_Db_Adapter_Exception */
            #require_once 'Zend/Db/Adapter/Exception.php';
            throw new \Zend_Db_Adapter_Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($this->offset);
        if ($offset < 0) {
            /** @see Zend_Db_Adapter_Exception */
            #require_once 'Zend/Db/Adapter/Exception.php';
            throw new \Zend_Db_Adapter_Exception("LIMIT argument offset=$offset is not valid");
        }

        $sql .= " LIMIT $count";
        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }
        return trim($sql);
    }
}
