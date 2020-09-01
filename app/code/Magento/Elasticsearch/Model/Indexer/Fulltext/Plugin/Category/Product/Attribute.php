<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Indexer\Fulltext\Plugin\Category\Product;

use Magento\Catalog\Model\ResourceModel\Attribute as AttributeResourceModel;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\Model\Indexer\IndexerHandler as ElasticsearchIndexerHandler;
use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;

/**
 * Catalog search indexer plugin for catalog attribute.
 */
class Attribute
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Processor
     */
    private $indexerProcessor;

    /**
     * @var DimensionProviderInterface
     */
    private $dimensionProvider;

    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var bool
     */
    private $isNewObject;

    /**
     * @var string
     */
    private $attributeCode;

    /**
     * @param Config $config
     * @param Processor $indexerProcessor
     * @param DimensionProviderInterface $dimensionProvider
     * @param IndexerHandlerFactory $indexerHandlerFactory
     */
    public function __construct(
        Config $config,
        Processor $indexerProcessor,
        DimensionProviderInterface $dimensionProvider,
        IndexerHandlerFactory $indexerHandlerFactory
    ) {
        $this->config = $config;
        $this->indexerProcessor = $indexerProcessor;
        $this->dimensionProvider = $dimensionProvider;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
    }

    /**
     * Update catalog search indexer mapping if third party search engine is used.
     *
     * @param AttributeResourceModel $subject
     * @param AttributeResourceModel $result
     * @return AttributeResourceModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function afterSave(
        AttributeResourceModel $subject,
        AttributeResourceModel $result
    ): AttributeResourceModel {
        $indexer = $this->indexerProcessor->getIndexer();
        if ($this->isNewObject
            && !$indexer->isScheduled()
            && $this->config->isElasticsearchEnabled()
        ) {
            $indexerHandler = $this->indexerHandlerFactory->create(['data' => $indexer->getData()]);
            if (!$indexerHandler instanceof ElasticsearchIndexerHandler) {
                throw new LocalizedException(
                    __('Created indexer handler must be instance of %1.', ElasticsearchIndexerHandler::class)
                );
            }
            foreach ($this->dimensionProvider->getIterator() as $dimension) {
                $indexerHandler->updateIndex($dimension, $this->attributeCode);
            }
        }

        return $result;
    }

    /**
     * Set class variables before saving attribute.
     *
     * @param AttributeResourceModel $subject
     * @param AbstractModel $attribute
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        AttributeResourceModel $subject,
        AbstractModel $attribute
    ): void {
        $this->isNewObject = $attribute->isObjectNew();
        $this->attributeCode = $attribute->getAttributeCode();
    }
}
