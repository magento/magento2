<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Query;

use Magento\Framework\Search\Request\QueryInterface;

/**
 * Match Query
 */
class Match implements QueryInterface
{
    /**
     * Name
     *
     * @var string
     */
    protected $name;

    /**
     * Value
     *
     * @var string
     */
    protected $value;

    /**
     * Boost
     *
     * @var int|null
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
     */
    protected $matches = [];

    /**
     * @param string $name
     * @param string $value
     * @param int|null $boost
     * @param array $matches
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
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return QueryInterface::TYPE_MATCH;
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
     * Get Matches
     *
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }
}
