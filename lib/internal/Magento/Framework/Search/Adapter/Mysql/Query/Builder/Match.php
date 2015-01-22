<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Query\Builder;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface;
use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder;
use Magento\Framework\Search\Request\Query\Bool;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;

class Match implements QueryInterface
{
    /**
     * @var string[]
     */
    private $replaceSymbols = ['-', '+', '~'];

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @param ResolverInterface $resolver
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

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
        $queryValue = $this->prepareQuery($query->getValue(), $conditionType);

        $fieldList = [];
        foreach ($query->getMatches() as $match) {
            $fieldList[] = $match['field'];
        }
        $resolvedFieldList = $this->resolver->resolve($fieldList);

        $queryBoost = $query->getBoost();
        $scoreBuilder->addCondition(
            $select->getMatchQuery($resolvedFieldList, $queryValue, Select::FULLTEXT_MODE_BOOLEAN),
            !is_null($queryBoost) ? $queryBoost : 1
        );
        $select->match($resolvedFieldList, $queryValue, true, Select::FULLTEXT_MODE_BOOLEAN);

        return $select;
    }

    /**
     * @param string $queryValue
     * @param string $conditionType
     * @return string
     */
    protected function prepareQuery($queryValue, $conditionType)
    {
        $queryValue = str_replace($this->replaceSymbols, ' ', $queryValue);

        $stringPrefix = '';
        if ($conditionType === Bool::QUERY_CONDITION_MUST) {
            $stringPrefix = '+';
        } elseif ($conditionType === Bool::QUERY_CONDITION_NOT) {
            $stringPrefix = '-';
        }

        $queryValues = explode(' ', $queryValue);

        foreach ($queryValues as $queryKey => $queryValue) {
            if (empty($queryValue)) {
                unset($queryValues[$queryKey]);
            } else {
                $queryValues[$queryKey] = $stringPrefix . $queryValue . '*';
            }
        }

        $queryValue = implode(' ', $queryValues);

        return $queryValue;
    }
}
