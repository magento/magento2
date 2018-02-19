<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use \Magento\Framework\App\ObjectManager;
use \Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryResolver
     */
    private $directoryResolver;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryResolver|null $directoryResolver
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Filesystem\DirectoryResolver $directoryResolver = null
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->directoryResolver = $directoryResolver
            ?: ObjectManager::getInstance()->get(\Magento\Framework\App\Filesystem\DirectoryResolver::class);
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Files upload processing
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $this->_initAction();
            $path = $this->getStorage()->getSession()->getCurrentPath();
            if (!$this->directoryResolver->validatePath($path, DirectoryList::MEDIA)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Directory %1 is not under storage root path.', $path)
                );
            }
            $result = $this->getStorage()->uploadFile($path, $this->getRequest()->getParam('type'));
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
