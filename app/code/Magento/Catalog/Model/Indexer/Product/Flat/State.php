<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

/**
 * @api
 * @since 2.0.0
 */
class State extends \Magento\Catalog\Model\Indexer\AbstractFlatState
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'catalog_product_flat';

    /**
     * Flat Is Enabled Config XML Path
     */
    const INDEXER_ENABLED_XML_PATH = 'catalog/frontend/flat_catalog_product';

    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     * @since 2.0.0
     */
    protected $_productFlatIndexerHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $flatIndexerHelper
     * @param bool $isAvailable
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Catalog\Helper\Product\Flat\Indexer $flatIndexerHelper,
        $isAvailable = false
    ) {
        parent::__construct($scopeConfig, $indexerRegistry, $isAvailable);
        $this->_productFlatIndexerHelper = $flatIndexerHelper;
    }

    /**
     * @return \Magento\Catalog\Helper\Product\Flat\Indexer
     * @since 2.0.0
     */
    public function getFlatIndexerHelper()
    {
        return $this->_productFlatIndexerHelper;
    }
}
