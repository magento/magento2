<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model;

class SitemapItem implements SitemapItemInterface
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $priority;

    /**
     * @var string
     */
    private $changeFrequency;

    /**
     * @var array
     */
    private $images;

    /**
     * @var string
     */
    private $updatedAt;

    /**
     * SitemapItem constructor.
     *
     * @param string $url
     * @param string $priority
     * @param string $changeFrequency
     * @param string|null $updatedAt
     * @param array|null $images
     */
    public function __construct($url, $priority, $changeFrequency, $updatedAt = null, $images = null)
    {
        $this->url = $url;
        $this->priority = $priority;
        $this->changeFrequency = $changeFrequency;
        $this->updatedAt = $updatedAt;
        $this->images = $images;
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
