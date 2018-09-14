<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model\ResourceModel;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sitemap\Api\SitemapRepositoryInterface;
use Magento\Sitemap\Api\Data\SitemapInterface;

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
     * SitemapRepository constructor.
     * @param Sitemap $sitemapResource
     * @param SitemapFactory $sitemapFactory
     */
    public function __construct(Sitemap $sitemapResource, \Magento\Sitemap\Model\SitemapFactory $sitemapFactory)
    {

        $this->sitemapResource = $sitemapResource;
        $this->sitemapFactory = $sitemapFactory;
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
    public function getList(): array
    {
        // TODO: Implement getList() method.
    }

    /**
     * {@inheritdoc}
     */
    public function save(SitemapInterface $sitemap)
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
    public function delete(SitemapInterface $sitemap)
    {
        try {
            $this->sitemapResource->save($sitemap);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Failed to delete sitemap with id: %1', $sitemap->getId()));
        }

        return true;
    }
}