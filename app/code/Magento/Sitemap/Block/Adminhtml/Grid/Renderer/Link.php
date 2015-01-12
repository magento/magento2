<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sitemap grid link column renderer
 *
 */
namespace Magento\Sitemap\Block\Adminhtml\Grid\Renderer;

use Magento\Framework\App\Filesystem\DirectoryList;

class Link extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Framework\Filesystem $filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Sitemap\Model\SitemapFactory
     */
    protected $_sitemapFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Sitemap\Model\SitemapFactory $sitemapFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Sitemap\Model\SitemapFactory $sitemapFactory,
        \Magento\Framework\Filesystem $filesystem,
        array $data = []
    ) {
        $this->_sitemapFactory = $sitemapFactory;
        $this->_filesystem = $filesystem;
        parent::__construct($context, $data);
    }

    /**
     * Prepare link to display in grid
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
    {
        /** @var $sitemap \Magento\Sitemap\Model\Sitemap */
        $sitemap = $this->_sitemapFactory->create();
        $url = $this->escapeHtml($sitemap->getSitemapUrl($row->getSitemapPath(), $row->getSitemapFilename()));

        $fileName = preg_replace('/^\//', '', $row->getSitemapPath() . $row->getSitemapFilename());
        $directory = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT);
        if ($directory->isFile($fileName)) {
            return sprintf('<a href="%1$s">%1$s</a>', $url);
        }

        return $url;
    }
}
