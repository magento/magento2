<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

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
     */
    protected $_productFlatIndexerHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Indexer\Model\IndexerInterface $flatIndexer
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $flatIndexerHelper
     * @param bool $isAvailable
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Indexer\Model\IndexerInterface $flatIndexer,
        \Magento\Catalog\Helper\Product\Flat\Indexer $flatIndexerHelper,
        $isAvailable = false
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->flatIndexer = $flatIndexer;
        $this->_productFlatIndexerHelper = $flatIndexerHelper;
        $this->isAvailable = $isAvailable;
        parent::__construct($scopeConfig, $flatIndexer, $isAvailable);
    }

    /**
     * @return \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    public function getFlatIndexerHelper()
    {
        return $this->_productFlatIndexerHelper;
    }
}
