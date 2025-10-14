<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Plugin;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Indexer\DeferredCacheCleanerInterface;
use Magento\ImportExport\Model\Import;

class DeferCacheCleaningUntilImportIsComplete
{
    private const BEHAVIOR_ADD_UPDATE = 'add_update';
    private const ENTITY_CUSTOMER = 'customer';
    private const CACHE_TYPE_GRAPHQL_QUERY_RESOLVER_RESULT = 'graphql_query_resolver_result';

    /**
     * @var DeferredCacheCleanerInterface
     */
    private $cacheCleaner;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param DeferredCacheCleanerInterface $cacheCleaner
     * @param TypeListInterface $cacheTypeList
     * @param RequestInterface $request
     */
    public function __construct(
        DeferredCacheCleanerInterface $cacheCleaner,
        TypeListInterface $cacheTypeList,
        RequestInterface $request
    ) {
        $this->cacheCleaner = $cacheCleaner;
        $this->cacheTypeList = $cacheTypeList;
        $this->request = $request;
    }

    /**
     * Start deferred cache before stock items save
     *
     * @param Import $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeImportSource(Import $subject): void
    {
        $this->cacheCleaner->start();
    }

    /**
     * Flush deferred cache after stock items save
     *
     * @param Import $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterImportSource(Import $subject, bool $result): bool
    {
        $behavior = $this->request->getParam('behavior');
        $entity = $this->request->getParam('entity');

        if ($behavior === self::BEHAVIOR_ADD_UPDATE &&
            $entity === self::ENTITY_CUSTOMER) {
            $this->cacheTypeList->cleanType(self::CACHE_TYPE_GRAPHQL_QUERY_RESOLVER_RESULT);
        }
        $this->cacheCleaner->flush();
        return $result;
    }
}
