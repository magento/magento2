<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CmsUrlRewrite\Plugin\Cms\Model\Store;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ResourceModel\Store;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Plugin which is listening store resource model and on save replace cms page url rewrites
 *
 * @see Store
 */
class View
{
    /**
     * @var AbstractModel
     */
    private $origStore;

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
     * Get the correct store for later regenerate url
     *
     * @param Store $object
     * @param AbstractModel $store
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        Store $object,
        AbstractModel $store
    ) {
        $this->origStore = $store;
    }

    /**
     * Regenerate urls on store after save
     *
     * @param Store $store
     * @return Store
     */
    public function afterSave(
        Store $store
    ) {
        if ($this->origStore->isObjectNew() || $this->origStore->dataHasChangedFor('group_id')) {
            if (!$this->origStore->isObjectNew()) {
                $this->urlPersist->deleteByData([UrlRewrite::STORE_ID => $this->origStore->getId()]);
            }
            $this->urlPersist->replace(
                $this->generateCmsPagesUrls($this->origStore->getId())
            );
        }
        return $store;
    }

    /**
     * Generate url rewrites for cms pages to store view
     *
     * @param int $storeId
     * @return array
     */
    private function generateCmsPagesUrls($storeId): array
    {
        $urls = [];
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $cmsPagesCollection = $this->pageRepository->getList($searchCriteria)->getItems();
        foreach ($cmsPagesCollection as $page) {
            $page->setStoreId($storeId);
            /** @var \Magento\Cms\Model\Page $page */
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $urls = array_merge(
                $urls,
                $this->cmsPageUrlRewriteGenerator->generate($page)
            );
        }
        return $urls;
    }
}
