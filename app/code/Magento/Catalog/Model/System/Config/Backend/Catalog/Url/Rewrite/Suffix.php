<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * URL suffix backend model
 *
 * @api
 *
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
     * @var \Magento\Framework\App\Config
     */
    private $appConfig;

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
     * Get instance of ScopePool
     *
     * @return \Magento\Framework\App\Config
     * @deprecated
     */
    private function getAppConfig()
    {
        if ($this->appConfig === null) {
            $this->appConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config::class
            );
        }
        return $this->appConfig;
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
                $this->cacheTypeList->invalidate([
                    \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER,
                    \Magento\Framework\App\Cache\Type\Collection::TYPE_IDENTIFIER
                ]);
            }
        }
        return parent::afterSave();
    }

    /**
     * {@inheritdoc}
     */
    public function afterDeleteCommit()
    {
        if ($this->isValueChanged()) {
            $this->updateSuffixForUrlRewrites();
            if ($this->isCategorySuffixChanged()) {
                $this->cacheTypeList->invalidate([
                    \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER,
                    \Magento\Framework\App\Cache\Type\Collection::TYPE_IDENTIFIER
                ]);
            }
        }

        return parent::afterDeleteCommit();
    }

    /**
     * Check is category suffix changed
     *
     * @return bool
     */
    private function isCategorySuffixChanged()
    {
        return $this->isValueChanged()
            && ($this->getPath() == CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX);
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
        if ($this->getValue() !== null) {
            $suffix = $this->getValue();
        } else {
            $this->getAppConfig()->clean();
            $suffix = $this->_config->getValue($this->getPath());
        }
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
