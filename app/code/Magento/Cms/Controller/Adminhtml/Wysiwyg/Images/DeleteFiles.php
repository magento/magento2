<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\App\ObjectManager;

class DeleteFiles extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images
{
    /**
     * Factory for json result.
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Factory for raw result.
     *
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryResolver
     */
    private $directoryResolver;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryResolver|null $directoryResolver
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\App\Filesystem\DirectoryResolver $directoryResolver = null
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->directoryResolver = $directoryResolver
            ?: ObjectManager::getInstance()->get(\Magento\Framework\App\Filesystem\DirectoryResolver::class);
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Delete file from media storage.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            if (!$this->getRequest()->isPost()) {
                throw new \Exception('Wrong request.');
            }
            $files = $this->getRequest()->getParam('files');

            /** @var $helper \Magento\Cms\Helper\Wysiwyg\Images */
            $helper = $this->_objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class);
            $path = $this->getStorage()->getSession()->getCurrentPath();
            if (!$this->directoryResolver->validatePath($path, DirectoryList::MEDIA)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Directory %1 is not under storage root path.', $path)
                );
            }
            foreach ($files as $file) {
                $file = $helper->idDecode($file);
                /** @var \Magento\Framework\Filesystem $filesystem */
                $filesystem = $this->_objectManager->get(\Magento\Framework\Filesystem::class);
                $dir = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $filePath = $path . '/' . \Magento\Framework\File\Uploader::getCorrectFileName($file);
                if ($dir->isFile($dir->getRelativePath($filePath))) {
                    $this->getStorage()->deleteFile($filePath);
                }
            }

            return $this->resultRawFactory->create();
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();

            return $resultJson->setData($result);
        }
    }
}
