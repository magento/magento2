<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Sitemap\Controller\Adminhtml\Sitemap;
use Magento\Sitemap\Model\SitemapFactory;

/**
 * Controller class Delete. Represents adminhtml request flow for a sitemap deletion
 */
class Delete extends Sitemap implements HttpPostActionInterface
{
    /**
     * @var SitemapFactory
     */
    private $sitemapFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Context $context
     * @param SitemapFactory $sitemapFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        SitemapFactory $sitemapFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->sitemapFactory = $sitemapFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function execute()
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('sitemap_id');
        if ($id) {
            try {
                // init model and delete
                /** @var \Magento\Sitemap\Model\Sitemap $sitemap */
                $sitemap = $this->sitemapFactory->create();
                $sitemap->load($id);
                // delete file
                $sitemapPath = $sitemap->getSitemapPath();
                if ($sitemapPath && $sitemapPath[0] === DIRECTORY_SEPARATOR) {
                    $sitemapPath = mb_substr($sitemapPath, 1);
                }
                $sitemapFilename = $sitemap->getSitemapFilename();
                $path = $directory->getRelativePath(
                    $sitemapPath .$sitemapFilename
                );
                if ($sitemap->getSitemapFilename() && $directory->isFile($path)) {
                    $directory->delete($path);
                }
                $sitemap->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the sitemap.'));
                // go to grid
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                $this->_redirect('adminhtml/*/edit', ['sitemap_id' => $id]);
                return;
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a sitemap to delete.'));
        // go to grid
        $this->_redirect('adminhtml/*/');
    }
}
