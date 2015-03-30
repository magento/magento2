<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Query\Builder;

use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface;
use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder;
use Magento\Framework\Search\Request\Query\Bool;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;

class Match implements QueryInterface
{
    const SPECIAL_CHARACTERS = '-+~/\\<>\'":*$#@()!,.?`=';

    const MINIMAL_CHARACTER_LENGTH = 3;

    /**
     * @var string[]
     */
    private $replaceSymbols = [];

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var Fulltext
     */
    private $fulltextHelper;

    /**
     * @param ResolverInterface $resolver
     * @param Fulltext $fulltextHelper
     */
    public function __construct(ResolverInterface $resolver, Fulltext $fulltextHelper)
    {
        $this->resolver = $resolver;
        $this->replaceSymbols = str_split(self::SPECIAL_CHARACTERS, 1);
        $this->fulltextHelper = $fulltextHelper;
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
            $this->fulltextHelper->getMatchQuery($resolvedFieldList, $queryValue, Fulltext::FULLTEXT_MODE_BOOLEAN),
            $queryBoost !== null ? $queryBoost : 1
        );
        $select = $this->fulltextHelper->match(
            $select,
            $resolvedFieldList,
            $queryValue,
            true,
            Fulltext::FULLTEXT_MODE_BOOLEAN
        );

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
                $stringSuffix = self::MINIMAL_CHARACTER_LENGTH > strlen($queryValue) ? '' : '*';
                $queryValues[$queryKey] = $stringPrefix . $queryValue . $stringSuffix;
            }
        }

        $queryValue = implode(' ', $queryValues);

        return $queryValue;
    }
}
