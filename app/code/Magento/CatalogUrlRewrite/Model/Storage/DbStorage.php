<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Storage;

use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\Product;
use Magento\UrlRewrite\Model\Storage\DbStorage as BaseDbStorage;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Psr\Log\LoggerInterface;

/**
 * Class DbStorage
 */
class DbStorage extends BaseDbStorage
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * DbStorage constructor.
     *
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourceConnection $resource
     * @param LoggerInterface|null $logger
     * @param ScopeConfigInterface|null $config
     * @param ProductFactory|null $productFactory
     */
    public function __construct(
        UrlRewriteFactory $urlRewriteFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceConnection $resource,
        LoggerInterface $logger = null,
        ScopeConfigInterface $config = null,
        ProductFactory $productFactory = null
    ) {
        parent::__construct($urlRewriteFactory, $dataObjectHelper, $resource, $logger);
        $this->config = $config ?? \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ScopeConfigInterface::class);
        $this->productFactory = $productFactory ?? \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ProductFactory::class);
    }

    /**
     * @inheritDoc
     */
    protected function prepareSelect(array $data)
    {
        $metadata = [];
        if (isset($data[UrlRewrite::METADATA])) {
            $metadata = $data[UrlRewrite::METADATA];
            unset($data[UrlRewrite::METADATA]);
        }
        $select = $this->connection->select();
        $select->from([
            'url_rewrite' => $this->resource->getTableName(self::TABLE_NAME)
        ]);
        $select->joinLeft(
            ['relation' => $this->resource->getTableName(Product::TABLE_NAME)],
            'url_rewrite.url_rewrite_id = relation.url_rewrite_id'
        );
        foreach ($data as $column => $value) {
            $select->where('url_rewrite.' . $column . ' IN (?)', $value);
        }
        if (empty($metadata['category_id'])) {
            $select->where('relation.category_id IS NULL');
        } else {
            $select->where(
                'relation.category_id = ?',
                $metadata['category_id']
            );
        }
        return $select;
    }

    /**
     * @inheritdoc
     */
    protected function doFindOneByData(array $data)
    {
        if (isset($data[UrlRewrite::REQUEST_PATH]) && is_string($data[UrlRewrite::REQUEST_PATH])) {
            return $this->findProductRewriteByRequestPath($data);
        }

        $filterResults = $this->findProductRewritesByFilter($data);
        if (!empty($filterResults)) {
            return reset($filterResults);
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    protected function doFindAllByData(array $data)
    {
        $rewrites = parent::doFindAllByData($data);

        $remainingProducts = [];
        if (isset($data[UrlRewrite::ENTITY_ID]) && is_array($data[UrlRewrite::ENTITY_ID])) {
            $remainingProducts = array_fill_keys($data[UrlRewrite::ENTITY_ID], 1);
            foreach ($rewrites as $rewrite) {
                $id = $rewrite[UrlRewrite::ENTITY_ID];
                if (isset($remainingProducts[$id])) {
                    unset($remainingProducts[$id]);
                }
            }
        }

        if (!empty($remainingProducts)) {
            $data[UrlRewrite::ENTITY_ID] = array_keys($remainingProducts);
            $rewrites = array_merge($rewrites, $this->findProductRewritesByFilter($data));
        }

        return $rewrites;
    }

    /**
     * Get category urlSuffix from config
     *
     * @param int $storeId
     * @return string
     */
    private function getCategoryUrlSuffix($storeId = null)
    {
        return $this->config->getValue(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Find product rewrite by request data
     *
     * @param array $data
     * @return array|null
     */
    private function findProductRewriteByRequestPath(array $data)
    {
        $requestPath = $data[UrlRewrite::REQUEST_PATH] ?? null;

        $productUrl = pathinfo($requestPath, PATHINFO_BASENAME);
        $data[UrlRewrite::REQUEST_PATH] = [$productUrl];

        $productFromDb = $this->connection->fetchRow($this->prepareSelect($data));
        if ($productFromDb === false) {
            return null;
        }
        $categorySuffix = $this->getCategoryUrlSuffix($data[UrlRewrite::STORE_ID]);
        $productResource = $this->productFactory->create();
        $categoryPath = str_replace('/' . $productUrl, '', $requestPath);
        if ($productFromDb[UrlRewrite::REDIRECT_TYPE]) {
            $productUrl = $productFromDb[UrlRewrite::TARGET_PATH];
        }
        if ($categoryPath) {
            $data[UrlRewrite::REQUEST_PATH] = [$categoryPath . $categorySuffix];
            unset($data[UrlRewrite::IS_AUTOGENERATED]);
            $categoryFromDb = $this->connection->fetchRow($this->prepareSelect($data));

            if ($categoryFromDb[UrlRewrite::REDIRECT_TYPE]) {
                // cat-a/cat-b ==> cat-a1/cat-b1
                $productFromDb[UrlRewrite::REDIRECT_TYPE] = \Magento\UrlRewrite\Model\OptionProvider::PERMANENT;
                $categoryPath = str_replace($categorySuffix, '', $categoryFromDb[UrlRewrite::TARGET_PATH]);
            }

            if ($categoryFromDb === false
                || !$productResource->canBeShowInCategory(
                    $productFromDb[UrlRewrite::ENTITY_ID],
                    $categoryFromDb[UrlRewrite::ENTITY_ID]
                )
            ) {
                return null;
            }

            $productFromDb[UrlRewrite::TARGET_PATH] = $productFromDb[UrlRewrite::TARGET_PATH]
                . '/category/' . $categoryFromDb[UrlRewrite::ENTITY_ID];
        }

        if ($productFromDb[UrlRewrite::REDIRECT_TYPE]) {
            $productFromDb[UrlRewrite::TARGET_PATH] = $categoryPath . '/' . $productUrl;
        }

        $productFromDb[UrlRewrite::REQUEST_PATH] = $requestPath;

        return $productFromDb;
    }

    /**
     * Find product rewrites by filter array
     *
     * @param array $data
     * @return array
     */
    private function findProductRewritesByFilter(array $data)
    {
        if (empty($data[UrlRewrite::ENTITY_TYPE]) || $data[UrlRewrite::ENTITY_TYPE] != 'product') {
            return [];
        }
        $rewrites = [];
        $metadata = $data[UrlRewrite::METADATA] ?? [];
        if (isset($data[UrlRewrite::METADATA])) {
            unset($data[UrlRewrite::METADATA]);
        }
        $productsFromDb = $this->connection->fetchAll($this->prepareSelect($data));

        if (!empty($metadata['category_id'])) {
            $categoryId = $metadata['category_id'];
            $data[UrlRewrite::ENTITY_ID] = $categoryId;
            $data[UrlRewrite::ENTITY_TYPE] = 'category';
            $categoryFromDb = $this->connection->fetchRow($this->prepareSelect($data));
            foreach ($productsFromDb as $productFromDb) {
                $productUrl = pathinfo($productFromDb[UrlRewrite::REQUEST_PATH], PATHINFO_BASENAME);
                $productFromDb[UrlRewrite::REQUEST_PATH] = str_replace(
                    $this->getCategoryUrlSuffix($data[UrlRewrite::STORE_ID]),
                    '',
                    $categoryFromDb[UrlRewrite::REQUEST_PATH]
                )
                    . '/' . $productUrl;
                $rewrites[] = $productFromDb;
            }
        } else {
            $rewrites = $productsFromDb;
        }

        return $rewrites;
    }
}
