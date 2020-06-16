<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adminhtml\System\Config\Backend;

use Magento\Framework\App\ObjectManager;

/**
 * Backend model for catalog search engine system config
 *
 * @api
 * @since 100.0.2
 */
class Engine extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Framework\Search\EngineResolverInterface
     */
    private $engineResolver;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Search\EngineResolverInterface|null $engineResolver
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Framework\Search\EngineResolverInterface $engineResolver = null
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->engineResolver = $engineResolver
            ?? ObjectManager::getInstance()->get(\Magento\Framework\Search\EngineResolverInterface::class);
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritDoc
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $value = (string)$this->getValue();
        if (empty($value)) {
            $defaultCountry = $this->engineResolver->getCurrentSearchEngine();
            $this->setValue($defaultCountry);
        }
        return $this;
    }

    /**
     * After save call
     *
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
