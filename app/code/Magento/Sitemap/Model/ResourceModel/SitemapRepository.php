<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model\ResourceModel;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sitemap\Api\Data\SitemapSearchResultsInterface;
use Magento\Sitemap\Api\SitemapRepositoryInterface;
use Magento\Sitemap\Api\Data\SitemapInterface;
use Magento\Sitemap\Model\SitemapFactory;

class SitemapRepository implements SitemapRepositoryInterface
{

    /**
     * @var Sitemap
     */
    private $sitemapResource;
    /**
     * @var SitemapFactory
     */
    private $sitemapFactory;
    /**
     * @var \Magento\Sitemap\Api\Data\SitemapSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * SitemapRepository constructor.
     * @param Sitemap $sitemapResource
     * @param SitemapFactory $sitemapFactory
     */
    public function __construct(
        Sitemap $sitemapResource,
        \Magento\Sitemap\Model\SitemapFactory $sitemapFactory,
        \Magento\Sitemap\Api\Data\SitemapSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {

        $this->sitemapResource = $sitemapResource;
        $this->sitemapFactory = $sitemapFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($sitemapId): SitemapInterface
    {
        $sitemap = $this->sitemapFactory->create();
        $this->sitemapResource->load($sitemap, $sitemapId);
        if (!$sitemap->getId()) {
            throw new NoSuchEntityException(
                __('The sitemap with "%1" ID doesn\'t exist.', $sitemapId)
            );
        }
        return $sitemap;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SitemapSearchResultsInterface
    {
        /** @var SitemapSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Magento\Sitemap\Model\ResourceModel\Sitemap\Collection $collection */
        $collection = $this->sitemapFactory->create()->getCollection();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setTotalCount($collection->getSize());

        $sitemaps = [];
        /** @var SitemapInterface $sitemap */
        foreach ($collection->getItems() as $sitemap) {
            $sitemaps[] = $this->getById($sitemap->getId());
        }
        $searchResults->setItems($sitemaps);

        return $searchResults;

    }

    /**
     * {@inheritdoc}
     */
    public function save(SitemapInterface $sitemap): int
    {
        try {
            $this->sitemapResource->save($sitemap);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $sitemap->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(SitemapInterface $sitemap): bool
    {
        try {
            $this->sitemapResource->save($sitemap);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Failed to delete sitemap with id: %1', $sitemap->getId()));
        }

        return true;
    }
}