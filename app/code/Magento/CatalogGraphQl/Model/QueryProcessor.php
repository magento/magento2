<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\StringUtils as StdlibString;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Search\Model\Query;
use Magento\Search\Model\QueryFactory;

/**
 * Prepares search query based on search text.
 */
class QueryProcessor
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
        $this->queryFactory  = $queryFactory;
        $this->string = $string;
    }

    /**
     * Prepare Query object based on search text
     *
     * @param ContextInterface $context
     * @param string $queryText
     * @throws NoSuchEntityException
     * @return Query
     */
    public function prepare(ContextInterface $context, string $queryText) : Query
    {
        $query = $this->queryFactory->create();
        $maxQueryLength = (int) $query->getMaxQueryLength();
        if ($maxQueryLength && $this->string->strlen($queryText) > $maxQueryLength) {
            $queryText = $this->string->substr($queryText, 0, $maxQueryLength);
        }
        $query->setQueryText($queryText);
        $store = $context->getExtensionAttributes()->getStore();
        $query->setStoreId($store->getId());
        return $query;
    }
}
