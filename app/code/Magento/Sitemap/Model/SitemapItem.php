<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model;

class SitemapItem implements SitemapItemInterface
{
    /**
     * SitemapItem constructor.
     *
     * @param string $url
     * @param string $priority
     * @param string $changeFrequency
     * @param string|null $updatedAt
     * @param array|null $images
     */
    public function __construct(
        private $url,
        private $priority,
        private $changeFrequency,
        private $updatedAt = null,
        private $images = null
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function getChangeFrequency()
    {
        return $this->changeFrequency;
    }

    /**
     * {@inheritdoc}
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
