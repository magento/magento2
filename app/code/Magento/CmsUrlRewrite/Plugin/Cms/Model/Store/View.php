<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Plugin\Cms\Model\Store;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ResourceModel\Store as ResourceStore;
use Magento\UrlRewrite\Model\UrlPersistInterface;

/**
 * Plugin which is listening store resource model and on save replace cms page url rewrites
 *
 * @see ResourceStore
 */
class View
{
    private const ALL_STORE_VIEWS = '0';

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var CmsPageUrlRewriteGenerator
     */
    private $cmsPageUrlRewriteGenerator;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Update store view plugin constructor
     *
     * @param UrlPersistInterface $urlPersist
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PageRepositoryInterface $pageRepository
     * @param CmsPageUrlRewriteGenerator $cmsPageUrlRewriteGenerator
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PageRepositoryInterface $pageRepository,
        CmsPageUrlRewriteGenerator $cmsPageUrlRewriteGenerator
    ) {
        $this->urlPersist = $urlPersist;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->pageRepository = $pageRepository;
        $this->cmsPageUrlRewriteGenerator = $cmsPageUrlRewriteGenerator;
    }

    /**
     * Replace cms page url rewrites on store view save
     *
     * @param ResourceStore $object
     * @param ResourceStore $result
     * @param ResourceStore $store
     * @return ResourceStore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(ResourceStore $object, ResourceStore $result, AbstractModel $store): ResourceStore
    {
        if ($store->isObjectNew()) {
            $this->urlPersist->replace(
                $this->generateCmsPagesUrls((int)$store->getId())
            );
        }

        return $result;
    }

    /**
     * Generate url rewrites for cms pages to store view
     *
     * @param int $storeId
     * @return array
     */
    private function generateCmsPagesUrls(int $storeId): array
    {
        $rewrites = [];
        $urls = [];

        foreach ($this->getCmsPageItems() as $page) {
            $page->setStoreId($storeId);
            $rewrites[] = $this->cmsPageUrlRewriteGenerator->generate($page);
        }
        $urls = array_merge($urls, ...$rewrites);

        return $urls;
    }

    /**
     * Return cms page items for all store view
     *
     * @return PageInterface[]
     */
    private function getCmsPageItems(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('store_id', self::ALL_STORE_VIEWS)
            ->create();
        $list = $this->pageRepository->getList($searchCriteria);

        return $list->getItems();
    }
}
