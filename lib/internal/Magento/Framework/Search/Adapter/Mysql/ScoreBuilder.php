<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

/**
 * Class for generating sql condition for calculating store manager
 */
class ScoreBuilder
{
    /**
     * @var string
     */
    private $scoreCondition = '';

    /**
     * @var string
     */
    const WEIGHT_FIELD = 'search_weight';

    /**
     * Get column alias for global score query in sql
     *
     * @return string
     */
    public function getScoreAlias()
    {
        return 'score';
    }

    /**
     * Get generated sql condition for global score
     *
     * @return string
     */
    public function build()
    {
        $scoreCondition = $this->scoreCondition;
        $this->clear();
        $scoreAlias = $this->getScoreAlias();

        return "({$scoreCondition}) AS {$scoreAlias}";
    }

    /**
     * Start Query
     *
     * @return void
     */
    public function startQuery()
    {
        $this->addPlus();
        $this->scoreCondition .= '(';
    }

    /**
     * End Query
     *
     * @param float $boost
     * @return void
     */
    public function endQuery($boost)
    {
        if (!empty($this->scoreCondition) && substr($this->scoreCondition, -1) !== '(') {
            $this->scoreCondition .= ") * {$boost}";
        } else {
            $this->scoreCondition .= '0)';
        }
    }

    /**
     * Add Condition for score calculation
     *
     * @param string $score
     * @param bool $useWeights
     * @return void
     */
    public function addCondition($score, $useWeights = true)
    {
        $this->addPlus();
        $condition = "{$score}";
        if ($useWeights) {
            $condition = "LEAST(($condition), 1000000) * POW(2, " . self::WEIGHT_FIELD . ')';
        }
        $this->scoreCondition .= $condition;
    }

    /**
     * Add Plus sign for Score calculation
     *
     * @return void
     */
    private function addPlus()
    {
        if (!empty($this->scoreCondition) && substr($this->scoreCondition, -1) !== '(') {
            $this->scoreCondition .= ' + ';
        }
    }

    /**
     * Clear score manager
     *
     * @return void
     */
    private function clear()
    {
        $this->scoreCondition = '';
    }
}
