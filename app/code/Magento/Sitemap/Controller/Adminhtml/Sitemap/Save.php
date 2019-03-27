<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Validator\StringLength;
use Magento\MediaStorage\Model\File\Validator\AvailablePath;
use Magento\Sitemap\Model\SitemapFactory;

/**
 * Save sitemap controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Sitemap\Controller\Adminhtml\Sitemap
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
     * @var \Magento\Sitemap\Helper\Data
     */
    private $sitemapHelper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var SitemapFactory
     */
    private $sitemapFactory;

    /**
     * @param Context $context
     * @param StringLength|null $stringValidator
     * @param AvailablePath|null $pathValidator
     * @param \Magento\Sitemap\Helper\Data|null $sitemapHelper
     * @param \Magento\Framework\Filesystem|null $filesystem
     * @param SitemapFactory|null $sitemapFactory
     */
    public function __construct(
        Context $context,
        StringLength $stringValidator = null,
        AvailablePath $pathValidator = null,
        \Magento\Sitemap\Helper\Data $sitemapHelper = null,
        \Magento\Framework\Filesystem $filesystem = null,
        SitemapFactory $sitemapFactory = null
    ) {
        parent::__construct($context);
        $this->stringValidator = $stringValidator ?: $this->_objectManager->get(StringLength::class);
        $this->pathValidator = $pathValidator ?: $this->_objectManager->get(AvailablePath::class);
        $this->sitemapHelper = $sitemapHelper ?: $this->_objectManager->get(\Magento\Sitemap\Helper\Data::class);
        $this->filesystem = $filesystem ?: $this->_objectManager->get(\Magento\Framework\Filesystem::class);
        $this->sitemapFactory = $sitemapFactory ?: $this->_objectManager->get(SitemapFactory::class);
    }

    /**
     * Validate path for generation
     *
     * @param array $data
     * @return bool
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
     * @return void
     */
    protected function clearSiteMap(\Magento\Sitemap\Model\Sitemap $model)
    {
        /** @var \Magento\Framework\Filesystem $directory */
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
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }

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
