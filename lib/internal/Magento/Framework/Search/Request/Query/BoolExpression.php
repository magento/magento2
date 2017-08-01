<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Query;

use Magento\Framework\Search\Request\QueryInterface;

/**
 * Bool Query
 * @api
 * @since 2.0.0
 */
class BoolExpression implements QueryInterface
{
    const QUERY_CONDITION_MUST = 'must';
    const QUERY_CONDITION_SHOULD = 'should';
    const QUERY_CONDITION_NOT = 'not';

    /**
     * Boost
     *
     * @var int|null
     * @since 2.0.0
     */
    protected $boost;

    /**
     * Query Name
     *
     * @var string
     * @since 2.0.0
     */
    protected $name;

    /**
     * Query names to which result set SHOULD satisfy
     *
     * @var array
     * @since 2.0.0
     */
    protected $should = [];

    /**
     * Query names to which result set MUST satisfy
     *
     * @var array
     * @since 2.0.0
     */
    protected $must = [];

    /**
     * Query names to which result set MUST NOT satisfy
     *
     * @var array
     * @since 2.0.0
     */
    protected $mustNot = [];

    /**
     * @param string $name
     * @param int|null $boost
     * @param array $must
     * @param array $should
     * @param array $not
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct($name, $boost, array $must = [], array $should = [], array $not = [])
    {
        $this->name = $name;
        $this->boost = $boost;
        $this->must = $must;
        $this->should = $should;
        $this->mustNot = $not;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getType()
    {
        return QueryInterface::TYPE_BOOL;
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
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * Get Should queries
     *
     * @return QueryInterface[]
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getShould()
    {
        return $this->should;
    }

    /**
     * Get Must queries
     *
     * @return QueryInterface[]
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getMust()
    {
        return $this->must;
    }

    /**
     * Get Must Not queries
     *
     * @return QueryInterface[]
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getMustNot()
    {
        return $this->mustNot;
    }
}
