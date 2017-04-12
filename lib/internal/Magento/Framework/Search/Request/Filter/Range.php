<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Filter;

use Magento\Framework\Search\Request\FilterInterface;

/**
 * Range Filter
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Range implements FilterInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var int
     */
    protected $from;

    /**
     * @var int
     */
    protected $to;

    /**
     * @param string $name
     * @param string $field
     * @param int $from
     * @param int $to
     */
    public function __construct($name, $field, $from, $to)
    {
        $this->name = $name;
        $this->field = $field;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return FilterInterface::TYPE_RANGE;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Get From
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Get To
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getTo()
    {
        return $this->to;
    }
}
