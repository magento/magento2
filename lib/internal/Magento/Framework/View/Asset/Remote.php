<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Asset;

/**
 * Page asset residing outside of the local file system
 */
class Remote implements AssetInterface
{
    /**
     * URL
     *
     * @var string
     */
    protected $url;

    /**
     * Content type
     *
     * @var string
     */
    protected $contentType;

    /**
     * Constructor
     *
     * @param string $url
     * @param string $contentType
     */
    public function __construct($url, $contentType = 'unknown')
    {
        $this->url = $url;
        $this->contentType = $contentType;
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
    public function getContentType()
    {
        return $this->contentType;
    }
}
