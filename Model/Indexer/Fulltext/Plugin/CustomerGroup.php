<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Indexer\Fulltext\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Customer\Model\ResourceModel\Group;
use Magento\Framework\Model\AbstractModel;
use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Search\Model\Engine;

class CustomerGroup extends AbstractPlugin
{
    /**
     * MySQL search engine
     */
    const CATALOG_SEARCH_MYSQL_ENGINE = 'MySQL';

    /**
     * @var ClientOptionsInterface
     */
    protected $clientOptions;

    /**
     * Current Search Engine
     * @var
     */
    protected $searchEngine;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param ClientOptionsInterface $clientOptions
     * @param Engine $searchEngine
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        ClientOptionsInterface $clientOptions,
        Engine $searchEngine
    ) {
        parent::__construct($indexerRegistry);
        $this->clientOptions = $clientOptions;
        $this->searchEngine = $searchEngine;
    }

    /**
     * Invalidate indexer on customer group save
     *
     * @param Group $subject
     * @param \Closure $proceed
     * @param AbstractModel $group
     * @return Attribute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        Group $subject,
        \Closure $proceed,
        AbstractModel $group
    ) {
        $needInvalidation = ($this->searchEngine->getCurrentSearchEngine() != self::CATALOG_SEARCH_MYSQL_ENGINE)
            && ($group->isObjectNew() || $group->dataHasChangedFor('tax_class_id'));
        $result = $proceed($group);
        if ($needInvalidation) {
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }
        return $result;
    }
}
