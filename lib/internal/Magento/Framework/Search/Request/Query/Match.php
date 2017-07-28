<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Query;

use Magento\Framework\Search\Request\QueryInterface;

/**
 * Match Query
 * @api
 * @since 2.0.0
 */
class Match implements QueryInterface
{
    /**
     * Name
     *
     * @var string
     * @since 2.0.0
     */
    protected $name;

    /**
     * Value
     *
     * @var string
     * @since 2.0.0
     */
    protected $value;

    /**
     * Boost
     *
     * @var int|null
     * @since 2.0.0
     */
    protected $boost;

    /**
     * Match query array
     * Possible structure:
     * array(
     *     ['field' => 'some_field', 'boost' => 'some_boost'],
     *     ['field' => 'some_field', 'boost' => 'some_boost'],
     * )
     *
     * @var array
     * @since 2.0.0
     */
    protected $matches = [];

    /**
     * @param string $name
     * @param string $value
     * @param int|null $boost
     * @param array $matches
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct($name, $value, $boost, array $matches)
    {
        $this->name = $name;
        $this->value = $value;
        $this->boost = $boost;
        $this->matches = $matches;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getType()
    {
        return QueryInterface::TYPE_MATCH;
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
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * Get Matches
     *
     * @return array
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getMatches()
    {
        return $this->matches;
    }
}
