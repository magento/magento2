<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Filter;

use Magento\Framework\Search\Request\FilterInterface;

/**
 * Bool Filter
 * @api
 */
class BoolExpression implements FilterInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * Filter names to which result set MUST satisfy
     *
     * @var array
     */
    protected $must = [];

    /**
     * Filter names to which result set SHOULD satisfy
     *
     * @var array
     */
    protected $should = [];

    /**
     * Filter names to which result set MUST NOT satisfy
     *
     * @var array
     */
    protected $mustNot = [];

    /**
     * @param string $name
     * @param array $must
     * @param array $should
     * @param array $not
     * @codeCoverageIgnore
     */
    public function __construct($name, array $must = [], array $should = [], array $not = [])
    {
        $this->name = $name;
        $this->must = $must;
        $this->should = $should;
        $this->mustNot = $not;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return FilterInterface::TYPE_BOOL;
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
     * Get Must filters
     *
     * @return \Magento\Framework\Search\Request\FilterInterface[]
     * @codeCoverageIgnore
     */
    public function getMust()
    {
        return $this->must;
    }

    /**
     * Get Should filters
     *
     * @return \Magento\Framework\Search\Request\FilterInterface[]
     * @codeCoverageIgnore
     */
    public function getShould()
    {
        return $this->should;
    }

    /**
     * Get Must Not filters
     *
     * @return \Magento\Framework\Search\Request\FilterInterface[]
     * @codeCoverageIgnore
     */
    public function getMustNot()
    {
        return $this->mustNot;
    }
}
