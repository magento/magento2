<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search;

use Magento\Framework\Stdlib\StringUtils as StdlibString;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Search\Model\QueryFactory;

/**
 * Query statics handler
 */
class QueryPopularity
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var StdlibString
     */
    private $string;

    /**
     * @param QueryFactory $queryFactory
     * @param StdlibString $string
     */
    public function __construct(
        QueryFactory $queryFactory,
        StdlibString $string
    ) {
        $this->queryFactory = $queryFactory;
        $this->string = $string;
    }

    /**
     * Fill the query popularity
     *
     * @param ContextInterface $context
     * @param string $queryText
     * @param int $numResults
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(ContextInterface $context, string $queryText, int $numResults) : void
    {
        $query = $this->queryFactory->create();
        $maxQueryLength = (int) $query->getMaxQueryLength();
        if ($maxQueryLength && $this->string->strlen($queryText) > $maxQueryLength) {
            $queryText = $this->string->substr($queryText, 0, $maxQueryLength);
        }
        $query->setQueryText($queryText);
        $store = $context->getExtensionAttributes()->getStore();
        $query->setStoreId($store->getId());
        $query->saveIncrementalPopularity();
        $query->saveNumResults($numResults);
    }
}
