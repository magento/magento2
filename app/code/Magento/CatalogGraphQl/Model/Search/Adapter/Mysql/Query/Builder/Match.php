<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Search\Adapter\Mysql\Query\Builder;

use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface;
use Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match as BuilderMatch;
use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Search\Helper\Data;

/**
 * @inheritdoc
 */
class Match extends BuilderMatch
{
    /**
     * @var Data
     */
    private $searchHelper;

    /**
     * @param ResolverInterface $resolver
     * @param Fulltext $fulltextHelper
     * @param Data $searchHelper
     * @param string $fulltextSearchMode
     * @param PreprocessorInterface[] $preprocessors
     */
    public function __construct(
        ResolverInterface $resolver,
        Fulltext $fulltextHelper,
        Data $searchHelper,
        $fulltextSearchMode = Fulltext::FULLTEXT_MODE_BOOLEAN,
        array $preprocessors = []
    ) {
        parent::__construct($resolver, $fulltextHelper, $fulltextSearchMode, $preprocessors);
        $this->searchHelper = $searchHelper;
    }

    /**
     * @inheritdoc
     */
    protected function prepareQuery($queryValue, $conditionType)
    {
        $replaceSymbols = str_split(self::SPECIAL_CHARACTERS, 1);
        $queryValue = str_replace($replaceSymbols, ' ', $queryValue);
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
                $stringSuffix = $this->searchHelper->getMinQueryLength() > strlen($queryValue) ? '' : '*';
                $queryValues[$queryKey] = $stringPrefix . $queryValue . $stringSuffix;
            }
        }

        $queryValue = implode(' ', $queryValues);

        return $queryValue;
    }
}
