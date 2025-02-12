<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * Base class for url rewrites tests logic
 *
 * @magentoDbIsolation enabled
 */
abstract class AbstractUrlRewriteTest extends TestCase
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var StoreRepositoryInterface */
    protected $storeRepository;

    /** @var ScopeConfigInterface */
    protected $config;

    /** @var UrlRewriteCollectionFactory */
    protected $urlRewriteCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeRepository = $this->objectManager->create(StoreRepositoryInterface::class);
        $this->config = $this->objectManager->get(ScopeConfigInterface::class);
        $this->urlRewriteCollectionFactory = $this->objectManager->get(UrlRewriteCollectionFactory::class);
    }

    /**
     * Retrieve all rewrite ids
     *
     * @return array
     */
    protected function getAllRewriteIds(): array
    {
        $urlRewriteCollection = $this->urlRewriteCollectionFactory->create();

        return $urlRewriteCollection->getAllIds();
    }

    /**
     * Check that actual data contains of expected values
     *
     * @param UrlRewriteCollection $collection
     * @param array $expectedData
     * @return void
     */
    protected function assertRewrites(UrlRewriteCollection $collection, array $expectedData): void
    {
        $collectionItems = $collection->toArray()['items'];
        $this->assertTrue(count($collectionItems) === count($expectedData));
        foreach ($expectedData as $expectedItem) {
            $found = false;
            foreach ($collectionItems as $item) {
                $found = array_intersect_assoc($item, $expectedItem) == $expectedItem;
                if ($found) {
                    break;
                }
            }
            $this->assertTrue($found, 'The actual data does not contains of expected values');
        }
    }

    /**
     * Get category url rewrites collection
     *
     * @param string|array $entityId
     * @return UrlRewriteCollection
     */
    protected function getEntityRewriteCollection($entityId): UrlRewriteCollection
    {
        $condition = is_array($entityId) ? ['in' => $entityId] : $entityId;
        $entityRewriteCollection = $this->urlRewriteCollectionFactory->create();
        $entityRewriteCollection->addFieldToFilter(UrlRewrite::ENTITY_ID, $condition)
            ->addFieldToFilter(UrlRewrite::ENTITY_TYPE, ['eq' => $this->getEntityType()]);

        return $entityRewriteCollection;
    }

    /**
     * Prepare expected data
     *
     * @param array $expectedData
     * @param int|null $id
     * @return array
     */
    protected function prepareData(array $expectedData, ?int $id = null): array
    {
        $newData = [];
        foreach ($expectedData as $key => $expectedItem) {
            $newData[$key] = str_replace(['%suffix%', '%id%'], [$this->getUrlSuffix(), $id], $expectedItem);
        }

        return $newData;
    }

    /**
     * Get entity type
     *
     * @return string
     */
    abstract protected function getEntityType(): string;

    /**
     * Get config value for url suffix
     *
     * @return string
     */
    abstract protected function getUrlSuffix(): string;
}
