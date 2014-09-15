<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->scoreCondition .= ") * {$boost}";
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
