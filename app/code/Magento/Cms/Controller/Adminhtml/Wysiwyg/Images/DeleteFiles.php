<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Delete image files.
 */
class DeleteFiles extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryResolver
     */
    private $directoryResolver;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryResolver|null $directoryResolver
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\App\Filesystem\DirectoryResolver $directoryResolver = null
    ) {
        parent::__construct($context, $coreRegistry);

        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->directoryResolver = $directoryResolver
            ?: $this->_objectManager->get(\Magento\Framework\App\Filesystem\DirectoryResolver::class);
    }

    /**
     * Delete file from media storage.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        if (!$this->getRequest()->isPost()) {
            $result = ['error' => true, 'message' => __('Wrong request.')];
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            return $resultJson->setData($result);
        }

        try {
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
                $filePath = $path . '/' . $file;
                if ($dir->isFile($dir->getRelativePath($filePath)) && !preg_match('#.htaccess#', $file)) {
                    $this->getStorage()->deleteFile($filePath);
                }
            }

            return $this->resultRawFactory->create();
            // phpcs:ignore Magento2.Exceptions.ThrowCatch
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];

            return $resultJson->setData($result);
        }
    }
}
