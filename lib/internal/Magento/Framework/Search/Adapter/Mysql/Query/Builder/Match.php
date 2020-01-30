<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Adapter\Mysql\Query\Builder;

use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Field\FieldInterface;
use Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface;
use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;

/**
 * MySQL search query match.
 *
 * @api
 * @deprecated 102.0.0
 * @see \Magento\ElasticSearch
 * @since 100.0.2
 */
class Match implements QueryInterface
{
    /**
     * @var string
     */
    const SPECIAL_CHARACTERS = '-+~/\\<>\'":*$#@()!,.?`=%&^';

    const MINIMAL_CHARACTER_LENGTH = 1;

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
     * @var string
     */
    private $fulltextSearchMode;

    /**
     * @var PreprocessorInterface[]
     * @since 100.1.0
     */
    protected $preprocessors;

    /**
     * @param ResolverInterface $resolver
     * @param Fulltext $fulltextHelper
     * @param string $fulltextSearchMode
     * @param PreprocessorInterface[] $preprocessors
     */
    public function __construct(
        ResolverInterface $resolver,
        Fulltext $fulltextHelper,
        $fulltextSearchMode = Fulltext::FULLTEXT_MODE_BOOLEAN,
        array $preprocessors = []
    ) {
        $this->resolver = $resolver;
        $this->replaceSymbols = str_split(self::SPECIAL_CHARACTERS, 1);
        $this->fulltextHelper = $fulltextHelper;
        $this->fulltextSearchMode = $fulltextSearchMode;
        $this->preprocessors = $preprocessors;
    }

    /**
     * @inheritdoc
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

        $fieldIds = [];
        $columns = [];
        foreach ($resolvedFieldList as $field) {
            if ($field->getType() === FieldInterface::TYPE_FULLTEXT && $field->getAttributeId()) {
                $fieldIds[] = $field->getAttributeId();
            }
            $column = $field->getColumn();
            $columns[$column] = $column;
        }

        $matchQuery = $this->fulltextHelper->getMatchQuery(
            $columns,
            $queryValue,
            $this->fulltextSearchMode
        );
        $scoreBuilder->addCondition($matchQuery, true);

        if ($fieldIds) {
            $matchQuery = sprintf('(%s AND search_index.attribute_id IN (%s))', $matchQuery, implode(',', $fieldIds));
        }

        $select->where($matchQuery);

        return $select;
    }

    /**
     * Prepare query value for build function.
     *
     * @param string $queryValue
     * @param string $conditionType
     * @return string
     */
    protected function prepareQuery($queryValue, $conditionType)
    {
        $queryValue = str_replace($this->replaceSymbols, ' ', $queryValue);
        foreach ($this->preprocessors as $preprocessor) {
            $queryValue = $preprocessor->process($queryValue);
        }

        $stringPrefix = '';
        if ($conditionType === BoolExpression::QUERY_CONDITION_MUST) {
            $stringPrefix = '+';
        } elseif ($conditionType === BoolExpression::QUERY_CONDITION_NOT) {
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
