<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * Page asset residing outside of the local file system
 * @since 2.0.0
 */
class Remote implements AssetInterface
{
    /**
     * URL
     *
     * @var string
     * @since 2.0.0
     */
    protected $url;

    /**
     * Content type
     *
     * @var string
     * @since 2.0.0
     */
    protected $contentType;

    /**
     * Constructor
     *
     * @param string $url
     * @param string $contentType
     * @since 2.0.0
     */
    public function __construct($url, $contentType = 'unknown')
    {
        $this->url = $url;
        $this->contentType = $contentType;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getSourceContentType()
    {
        return $this->getContentType();
    }
}
