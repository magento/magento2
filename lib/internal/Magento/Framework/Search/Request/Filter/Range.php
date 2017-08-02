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
 * @api
 * @since 2.0.0
 */
class Range implements FilterInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $name;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $field;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $from;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $to;

    /**
     * @param string $name
     * @param string $field
     * @param int $from
     * @param int $to
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getType()
    {
        return FilterInterface::TYPE_RANGE;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Field
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getTo()
    {
        return $this->to;
    }
}
