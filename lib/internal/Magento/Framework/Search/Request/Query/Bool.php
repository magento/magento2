<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Query;

use Magento\Framework\Search\Request\QueryInterface;

/**
 * Bool Query
 */
class Bool implements QueryInterface
{
    const QUERY_CONDITION_MUST = 'must';
    const QUERY_CONDITION_SHOULD = 'should';
    const QUERY_CONDITION_NOT = 'not';

    /**
     * Boost
     *
     * @var int|null
     */
    protected $boost;

    /**
     * Query Name
     *
     * @var string
     */
    protected $name;

    /**
     * Query names to which result set SHOULD satisfy
     *
     * @var array
     */
    protected $should = [];

    /**
     * Query names to which result set MUST satisfy
     *
     * @var array
     */
    protected $must = [];

    /**
     * Query names to which result set MUST NOT satisfy
     *
     * @var array
     */
    protected $mustNot = [];

    /**
     * @param string $name
     * @param int|null $boost
     * @param array $must
     * @param array $should
     * @param array $not
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
     */
    public function getType()
    {
        return QueryInterface::TYPE_BOOL;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * Get Should queries
     *
     * @return QueryInterface[]
     */
    public function getShould()
    {
        return $this->should;
    }

    /**
     * Get Must queries
     *
     * @return QueryInterface[]
     */
    public function getMust()
    {
        return $this->must;
    }

    /**
     * Get Must Not queries
     *
     * @return QueryInterface[]
     */
    public function getMustNot()
    {
        return $this->mustNot;
    }
}
