<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller;

class Save extends \Magento\Sitemap\Controller\Adminhtml\Sitemap
{
    /**
     * Validate path to generate
     *
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    protected function validatePathToGenerate(array $data)
    {
        if (!empty($data['sitemap_filename']) && !empty($data['sitemap_path'])) {
            $data['sitemap_path'] = '/' . ltrim($data['sitemap_path'], '/');
            $path = rtrim($data['sitemap_path'], '\\/') . '/' . $data['sitemap_filename'];
            /** @var $validator \Magento\MediaStorage\Model\File\Validator\AvailablePath */
            $validator = $this->_objectManager->create('Magento\MediaStorage\Model\File\Validator\AvailablePath');
            /** @var $helper \Magento\Sitemap\Helper\Data */
            $helper = $this->_objectManager->get('Magento\Sitemap\Helper\Data');
            $validator->setPaths($helper->getValidPaths());
            if (!$validator->isValid($path)) {
                foreach ($validator->getMessages() as $message) {
                    $this->messageManager->addError($message);
                }
                // save data in session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                // redirect to edit form
                return false;
            }
        }
        return true;
    }

    /**
     * Clear sitemap
     *
     * @param \Magento\Sitemap\Model\Sitemap $model
     * @return void
     */
    protected function clearSiteMap(\Magento\Sitemap\Model\Sitemap $model)
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write $directory */
        $directory = $this->_objectManager->get('Magento\Framework\Filesystem')
            ->getDirectoryWrite(DirectoryList::ROOT);

        if ($this->getRequest()->getParam('sitemap_id')) {
            $model->load($this->getRequest()->getParam('sitemap_id'));
            $fileName = $model->getSitemapFilename();

            $path = $model->getSitemapPath() . '/' . $fileName;
            if ($fileName && $directory->isFile($path)) {
                $directory->delete($path);
            }
        }
    }

    /**
     * Save model
     *
     * @param array $data
     * @return Controller\Result\Redirect|Controller\Result\Forward
     */
    protected function saveModel($data)
    {
        // init model and set data
        /** @var \Magento\Sitemap\Model\Sitemap $model */
        $model = $this->_objectManager->create('Magento\Sitemap\Model\Sitemap');
        $this->clearSiteMap($model);
        $model->setData($data);

        // try to save it
        try {
            // save the data
            $model->save();
            // display success message
            $this->messageManager->addSuccess(__('You saved the sitemap.'));
            // clear previously saved data from session
            $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

            // check if 'Save and Continue'
            if ($this->getRequest()->getParam('back')) {
                return $this->resultFactory->create(Controller\ResultFactory::TYPE_REDIRECT)
                    ->setPath('adminhtml/*/edit', ['sitemap_id' => $model->getId()]);
            }
            // go to grid or forward to generate action
            if ($this->getRequest()->getParam('generate')) {
                $this->getRequest()->setParam('sitemap_id', $model->getId());
                return $this->resultFactory->create(Controller\ResultFactory::TYPE_FORWARD)->forward('generate');
            }
            return $this->resultFactory->create(Controller\ResultFactory::TYPE_REDIRECT)->setPath('adminhtml/*/');
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addError($e->getMessage());
            // save data in session
            $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
            // redirect to edit form
            return $this->resultFactory->create(Controller\ResultFactory::TYPE_REDIRECT)
                ->setPath('adminhtml/*/edit', ['sitemap_id' => $this->getRequest()->getParam('sitemap_id')]);
        }
    }

    /**
     * Save action
     *
     * @return Controller\Result\Redirect
     */
    public function execute()
    {
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (!$this->validatePathToGenerate($data)) {
                /** @var Controller\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(Controller\ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath(
                    'adminhtml/*/edit',
                    ['sitemap_id' => $this->getRequest()->getParam('sitemap_id')]
                );
            }
            $this->saveModel($data);
        }
        return $this->resultFactory->create(Controller\ResultFactory::TYPE_REDIRECT)->setPath('adminhtml/*/');
    }
}
