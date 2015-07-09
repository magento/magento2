<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adminhtml\System\Config\Backend;

/**
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Engine extends \Magento\Framework\App\Config\Value
{
    /** @var \Magento\Indexer\Model\IndexerRegistry */
    protected $indexerRegistry;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Indexer\Model\IndexerRegistry $indexerRegistry
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Indexer\Model\IndexerRegistry $indexerRegistry,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->indexerRegistry = $indexerRegistry;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * After save call
     * Invalidate catalog search index if engine was changed
     *
     * @return $this
     */
    public function afterSave()
    {
        parent::afterSave();

        if ($this->isValueChanged()) {
            $this->indexerRegistry->get(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)->invalidate();
        }
        return $this;
    }
}
