<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Validator\StringLength;
use Magento\MediaStorage\Model\File\Validator\AvailablePath;
use Magento\Sitemap\Controller\Adminhtml\Sitemap;
use Magento\Sitemap\Helper\Data;
use Magento\Sitemap\Model\SitemapFactory;

/**
 * Save sitemap controller.
 */
class Save extends Sitemap implements HttpPostActionInterface
{
    /**
     * Maximum length of sitemap filename
     */
    const MAX_FILENAME_LENGTH = 32;

    /**
     * @var StringLength
     */
    private $stringValidator;

    /**
     * @var AvailablePath
     */
    private $pathValidator;

    /**
     * @var Data
     */
    private $sitemapHelper;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var SitemapFactory
     */
    private $sitemapFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param StringLength $stringValidator
     * @param AvailablePath $pathValidator
     * @param Data $sitemapHelper
     * @param Filesystem $filesystem
     * @param SitemapFactory $sitemapFactory
     */
    public function __construct(
        Context $context,
        StringLength $stringValidator,
        AvailablePath $pathValidator,
        Data $sitemapHelper,
        Filesystem $filesystem,
        SitemapFactory $sitemapFactory
    ) {
        parent::__construct($context);
        $this->stringValidator = $stringValidator;
        $this->pathValidator = $pathValidator;
        $this->sitemapHelper = $sitemapHelper;
        $this->filesystem = $filesystem;
        $this->sitemapFactory = $sitemapFactory;
    }

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
            $this->pathValidator->setPaths($this->sitemapHelper->getValidPaths());
            if (!$this->pathValidator->isValid($path)) {
                foreach ($this->pathValidator->getMessages() as $message) {
                    $this->messageManager->addErrorMessage($message);
                }
                // save data in session
                $this->_session->setFormData($data);
                // redirect to edit form
                return false;
            }

            $filename = rtrim($data['sitemap_filename']);
            $this->stringValidator->setMax(self::MAX_FILENAME_LENGTH);
            if (!$this->stringValidator->isValid($filename)) {
                foreach ($this->stringValidator->getMessages() as $message) {
                    $this->messageManager->addErrorMessage($message);
                }
                // save data in session
                $this->_session->setFormData($data);
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
     *
     * @return void
     */
    protected function clearSiteMap(\Magento\Sitemap\Model\Sitemap $model)
    {
        /** @var Filesystem $directory */
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);

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
        $model = $this->sitemapFactory->create();
        $this->clearSiteMap($model);
        $model->setData($data);

        // try to save it
        try {
            // save the data
            $model->save();
            // display success message
            $this->messageManager->addSuccessMessage(__('You saved the sitemap.'));
            // clear previously saved data from session
            $this->_session->setFormData(false);
            return $model->getId();
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());
            // save data in session
            $this->_session->setFormData($data);
        }
        return false;
    }

    /**
     * Get result after saving data
     *
     * @param string|bool $id
     * @return ResultInterface
     */
    protected function getResult($id)
    {
        /** @var Redirect $resultRedirect */
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
     * @return Redirect
     */
    public function execute()
    {
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        /** @var Redirect $resultRedirect */
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
