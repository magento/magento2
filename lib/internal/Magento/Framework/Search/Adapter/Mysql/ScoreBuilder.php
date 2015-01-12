<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Get column alias for global score query in sql
     *
     * @return string
     */
    public function getScoreAlias()
    {
        return 'global_score';
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
        if (!empty($this->scoreCondition) && substr($this->scoreCondition, -1) != '(') {
            $this->scoreCondition .= ") * {$boost}";
        } else {
            $this->scoreCondition .= '0)';
        }
    }

    /**
     * Add Condition for score calculation
     *
     * @param string $score
     * @param float $boost
     * @return void
     */
    public function addCondition($score, $boost)
    {
        $this->addPlus();
        $this->scoreCondition .= "{$score} * {$boost}";
    }

    /**
     * Add Plus sign for Score calculation
     *
     * @return void
     */
    private function addPlus()
    {
        if (!empty($this->scoreCondition) && substr($this->scoreCondition, -1) != '(') {
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
