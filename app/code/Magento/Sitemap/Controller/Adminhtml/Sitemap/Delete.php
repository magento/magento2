<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Delete extends \Magento\Sitemap\Controller\Adminhtml\Sitemap
{
    /**
     * Delete action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write $directory */
        $directory = $this->_objectManager->get(
            'Magento\Framework\Filesystem'
        )->getDirectoryWrite(
            DirectoryList::ROOT
        );

        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('sitemap_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create('Magento\Sitemap\Model\Sitemap');
                $model->setId($id);
                // init and load sitemap model

                /* @var $sitemap \Magento\Sitemap\Model\Sitemap */
                $model->load($id);
                // delete file
                $path = $directory->getRelativePath($model->getPreparedFilename());
                if ($model->getSitemapFilename() && $directory->isFile($path)) {
                    $directory->delete($path);
                }
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The sitemap has been deleted.'));
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
}
