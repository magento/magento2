<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller;

class Save extends \Magento\Sitemap\Controller\Adminhtml\Sitemap
{
    /**
     * Validate path for generation
     *
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    protected function validatePath(array $data)
    {
        if (!empty($data['sitemap_filename']) && !empty($data['sitemap_path'])) {
            $data['sitemap_path'] = '/' . ltrim($data['sitemap_path'], '/');
            $path = rtrim($data['sitemap_path'], '\\/') . '/' . $data['sitemap_filename'];
            /** @var $validator \Magento\MediaStorage\Model\File\Validator\AvailablePath */
            $validator = $this->_objectManager->create(\Magento\MediaStorage\Model\File\Validator\AvailablePath::class);
            /** @var $helper \Magento\Sitemap\Helper\Data */
            $helper = $this->_objectManager->get(\Magento\Sitemap\Helper\Data::class);
            $validator->setPaths($helper->getValidPaths());
            if (!$validator->isValid($path)) {
                foreach ($validator->getMessages() as $message) {
                    $this->messageManager->addError($message);
                }
                // save data in session
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData($data);
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
        $directory = $this->_objectManager->get(\Magento\Framework\Filesystem::class)
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
     * Save data
     *
     * @param array $data
     * @return string|bool
     */
    protected function saveData($data)
    {
        // init model and set data
        /** @var \Magento\Sitemap\Model\Sitemap $model */
        $model = $this->_objectManager->create(\Magento\Sitemap\Model\Sitemap::class);
        $this->clearSiteMap($model);
        $model->setData($data);

        // try to save it
        try {
            // save the data
            $model->save();
            // display success message
            $this->messageManager->addSuccess(__('You saved the sitemap.'));
            // clear previously saved data from session
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);
            return $model->getId();
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addError($e->getMessage());
            // save data in session
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData($data);
        }
        return false;
    }

    /**
     * Get result after saving data
     *
     * @param string|bool $id
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function getResult($id)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(Controller\ResultFactory::TYPE_REDIRECT);
        if ($id) {
            // check if 'Save and Continue'
            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('adminhtml/*/edit', ['sitemap_id' => $id]);
                return $resultRedirect;
            }
            // go to grid or forward to generate action
            if ($this->getRequest()->getParam('generate')) {
                $this->getRequest()->setParam('sitemap_id', $id);
                return $this->resultFactory->create(Controller\ResultFactory::TYPE_FORWARD)
                    ->forward('generate');
            }
            $resultRedirect->setPath('adminhtml/*/');
            return $resultRedirect;
        }
        $resultRedirect->setPath(
            'adminhtml/*/edit',
            ['sitemap_id' => $this->getRequest()->getParam('sitemap_id')]
        );
        return $resultRedirect;
    }

    /**
     * Save action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(Controller\ResultFactory::TYPE_REDIRECT);
        if ($data) {
            if (!$this->validatePath($data)) {
                $resultRedirect->setPath(
                    'adminhtml/*/edit',
                    ['sitemap_id' => $this->getRequest()->getParam('sitemap_id')]
                );
                return $resultRedirect;
            }
            return $this->getResult($this->saveData($data));
        }
        $resultRedirect->setPath('adminhtml/*/');
        return $resultRedirect;
    }
}
