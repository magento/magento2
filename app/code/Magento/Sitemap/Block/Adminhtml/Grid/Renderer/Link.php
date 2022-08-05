<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Block\Adminhtml\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Sitemap\Model\Sitemap;
use Magento\Sitemap\Model\SitemapFactory;

/**
 * Sitemap grid link column renderer
 */
class Link extends AbstractRenderer
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var SitemapFactory
     */
    private $sitemapFactory;

    /**
     * @var DocumentRoot
     */
    private $documentRoot;

    /**
     * @param Context $context
     * @param SitemapFactory $sitemapFactory
     * @param Filesystem $filesystem
     * @param DocumentRoot $documentRoot
     * @param array $data
     */
    public function __construct(
        Context $context,
        SitemapFactory $sitemapFactory,
        Filesystem $filesystem,
        DocumentRoot $documentRoot,
        array $data = []
    ) {
        $this->sitemapFactory = $sitemapFactory;
        $this->filesystem = $filesystem;
        $this->documentRoot = $documentRoot;

        parent::__construct($context, $data);
    }

    /**
     * Prepare link to display in grid
     *
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        /** @var $sitemap Sitemap */
        $sitemap = $this->sitemapFactory->create();
        $sitemap->setStoreId($row->getStoreId());
        $url = $this->_escaper->escapeHtml($sitemap->getSitemapUrl($row->getSitemapPath(), $row->getSitemapFilename()));

        $fileName = preg_replace('/^\//', '', $row->getSitemapPath() . $row->getSitemapFilename());
        $documentRootPath = $this->documentRoot->getPath();
        $directory = $this->filesystem->getDirectoryRead($documentRootPath);
        if ($directory->isFile($fileName)) {
            return sprintf('<a href="%1$s">%1$s</a>', $url);
        }

        return $url;
    }
}
