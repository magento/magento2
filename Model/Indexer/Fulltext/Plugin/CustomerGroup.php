<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
use Magento\Search\Model\EngineResolver;

class CustomerGroup extends AbstractPlugin
{
    /**
     * @var ClientOptionsInterface
     */
    protected $clientOptions;

    /**
     * @var EngineResolver
     */
    protected $engineResolver;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param ClientOptionsInterface $clientOptions
     * @param EngineResolver $engineResolver
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        ClientOptionsInterface $clientOptions,
        EngineResolver $engineResolver
    ) {
        parent::__construct($indexerRegistry);
        $this->clientOptions = $clientOptions;
        $this->engineResolver = $engineResolver;
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
        $needInvalidation =
            ($this->engineResolver->getCurrentSearchEngine() != EngineResolver::CATALOG_SEARCH_MYSQL_ENGINE)
            && ($group->isObjectNew() || $group->dataHasChangedFor('tax_class_id'));
        $result = $proceed($group);
        if ($needInvalidation) {
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }
        return $result;
    }
}
