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
namespace Magento\Framework\Search\Adapter\Mysql\Query\Builder;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder;
use Magento\Framework\Search\Request\Query\Bool;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;

class Match implements QueryInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(
        ScoreBuilder $scoreBuilder,
        Select $select,
        RequestQueryInterface $query,
        $conditionType
    ) {
        /** @var $query \Magento\Framework\Search\Request\Query\Match */
        $queryValue = $query->getValue();
        if ($conditionType === Bool::QUERY_CONDITION_MUST) {
            $queryValue = '+' . $queryValue;
        } elseif ($conditionType === Bool::QUERY_CONDITION_NOT) {
            $queryValue = '-' . $queryValue;
        }

        $fieldList = [];
        foreach ($query->getMatches() as $match) {
            $fieldList[] = $match['field'];
        }

        $queryBoost = $query->getBoost();
        $scoreBuilder->addCondition(
            $select->getMatchQuery($fieldList, $queryValue, Select::FULLTEXT_MODE_BOOLEAN),
            !is_null($queryBoost) ? $queryBoost : 1
        );
        $select->match($fieldList, $queryValue, true, Select::FULLTEXT_MODE_BOOLEAN);

        return $select;
    }
}
