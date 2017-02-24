<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Delete extends \Magento\Sitemap\Controller\Adminhtml\Sitemap
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Sitemap\Model\SitemapFactory
     */
    private $sitemapFactory;

    /**
     * Delete action
     *
     * @return void
     */
    public function execute()
    {
        $directory = $this->getFilesystem()->getDirectoryWrite(DirectoryList::ROOT);
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('sitemap_id');
        if ($id) {
            try {
                // init model and delete
                /** @var \Magento\Sitemap\Model\Sitemap $sitemap */
                $sitemap = $this->getSitemapFactory()->create();
                $sitemap->load($id);
                // delete file
                $sitemapPath = $sitemap->getSitemapPath();
                $sitemapFilename = $sitemap->getSitemapFilename();

                $path = $directory->getRelativePath($sitemapPath . $sitemapFilename);
                if ($sitemap->getSitemapFilename() && $directory->isFile($path)) {
                    $directory->delete($path);
                }
                $sitemap->delete();
                // display success message
                $this->messageManager->addSuccess(__('You deleted the sitemap.'));
                // go to grid
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('adminhtml/*/edit', ['sitemap_id' => $id]);
                return;
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a sitemap to delete.'));
        // go to grid
        $this->_redirect('adminhtml/*/');
    }

    /**
     * The getter function to get Filesystem object for real application code
     *
     * @return \Magento\Framework\Filesystem
     *
     * @deprecated
     */
    private function getFilesystem()
    {
        if ($this->filesystem === null) {
            $this->filesystem = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Filesystem::class
            );
        }
        return $this->filesystem;
    }

    /**
     * The getter function to get SitemapFactory object for real application code
     *
     * @return \Magento\Sitemap\Model\SitemapFactory
     *
     * @deprecated
     */
    private function getSitemapFactory()
    {
        if ($this->sitemapFactory === null) {
            $this->sitemapFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Sitemap\Model\SitemapFactory::class
            );
        }
        return $this->sitemapFactory;
    }
}
