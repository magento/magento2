<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Url rewrite suffix backend
 */
namespace Magento\Catalog\Model\System\Config\Backend\Catalog\Url\Rewrite;

use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\ScopeInterface;
use Magento\UrlRewrite\Model\Storage\DbStorage;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Suffix extends \Magento\Framework\App\Config\Value
{
    /** @var \Magento\UrlRewrite\Helper\UrlRewrite */
    protected $urlRewriteHelper;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var \Magento\UrlRewrite\Model\UrlFinderInterface */
    protected $urlFinder;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface */
    protected $connection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\UrlRewrite\Helper\UrlRewrite $urlRewriteHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ResourceConnection $appResource
     * @param \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\UrlRewrite\Helper\UrlRewrite $urlRewriteHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $appResource,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->urlRewriteHelper = $urlRewriteHelper;
        $this->connection = $appResource->getConnection();
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
        $this->resource = $appResource;
    }

    /**
     * Check url rewrite suffix - whether we can support it
     *
     * @return $this
     */
    public function beforeSave()
    {
        $this->urlRewriteHelper->validateSuffix($this->getValue());
        return $this;
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->updateSuffixForUrlRewrites();
            if ($this->isCategorySuffixChanged()) {
                $this->invalidateCategoryRelatedCache();
            }
        }
        return parent::afterSave();
    }

    /**
     * Check is category suffix changed
     *
     * @return bool
     */
    protected function isCategorySuffixChanged()
    {
        return  $this->isValueChanged() 
            && ($this->getPath() == CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX);
    }

    /**
     * Invalidate cache that store old category suffix
     *
     * @return void
     */
    protected function invalidateCategoryRelatedCache()
    {
        $this->cacheTypeList->invalidate([
            \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER,
            \Magento\Framework\App\Cache\Type\Collection::TYPE_IDENTIFIER
        ]);
    }

    /**
     * Update suffix for url rewrites
     *
     * @return $this
     */
    protected function updateSuffixForUrlRewrites()
    {
        $map = [
            ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX => ProductUrlRewriteGenerator::ENTITY_TYPE,
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX => CategoryUrlRewriteGenerator::ENTITY_TYPE,
        ];
        if (!isset($map[$this->getPath()])) {
            return $this;
        }
        $dataFilter = [UrlRewrite::ENTITY_TYPE => $map[$this->getPath()]];
        $storesIds = $this->getStoreIds();
        if ($storesIds) {
            $dataFilter[UrlRewrite::STORE_ID] = $storesIds;
        }
        $entities = $this->urlFinder->findAllByData($dataFilter);
        $oldSuffixPattern = '~' . preg_quote($this->getOldValue()) . '$~';
        $suffix = $this->getValue();
        foreach ($entities as $urlRewrite) {
            $bind = $urlRewrite->getIsAutogenerated()
                ? [UrlRewrite::REQUEST_PATH => preg_replace($oldSuffixPattern, $suffix, $urlRewrite->getRequestPath())]
                : [UrlRewrite::TARGET_PATH => preg_replace($oldSuffixPattern, $suffix, $urlRewrite->getTargetPath())];
            $this->connection->update(
                $this->resource->getTableName(DbStorage::TABLE_NAME),
                $bind,
                $this->connection->quoteIdentifier(UrlRewrite::URL_REWRITE_ID) . ' = ' . $urlRewrite->getUrlRewriteId()
            );
        }
        return $this;
    }

    /**
     * @return array|null
     */
    protected function getStoreIds()
    {
        if ($this->getScope() == 'stores') {
            $storeIds = [$this->getScopeId()];
        } elseif ($this->getScope() == 'websites') {
            $website = $this->storeManager->getWebsite($this->getScopeId());
            $storeIds = array_keys($website->getStoreIds());
            $storeIds = array_diff($storeIds, $this->getOverrideStoreIds($storeIds));
        } else {
            $storeIds = array_keys($this->storeManager->getStores());
            $storeIds = array_diff($storeIds, $this->getOverrideStoreIds($storeIds));
        }
        return $storeIds;
    }

    /**
     * @param array $storeIds
     * @return array
     */
    protected function getOverrideStoreIds($storeIds)
    {
        $excludeIds = [];
        foreach ($storeIds as $storeId) {
            $suffix = $this->_config->getValue($this->getPath(), ScopeInterface::SCOPE_STORE, $storeId);
            if ($suffix != $this->getOldValue()) {
                $excludeIds[] = $storeId;
            }
        }
        return $excludeIds;
    }
}
