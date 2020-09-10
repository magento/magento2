<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

class CustomerGroup extends AbstractPlugin
{
    /**
     * @var ClientOptionsInterface
     */
    protected $clientOptions;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param ClientOptionsInterface $clientOptions
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        ClientOptionsInterface $clientOptions
    ) {
        parent::__construct($indexerRegistry);
        $this->clientOptions = $clientOptions;
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
        $needInvalidation = $group->isObjectNew() || $group->dataHasChangedFor('tax_class_id');
        $result = $proceed($group);
        if ($needInvalidation) {
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }
        return $result;
    }
}
