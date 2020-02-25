<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Delete image folder.
 */
class DeleteFolder extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images implements HttpPostActionInterface
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
     * Delete folder action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $path = $this->getStorage()->getCmsWysiwygImages()->getCurrentPath();
            $this->getStorage()->deleteDirectory($path);

            return $this->resultRawFactory->create();
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();

            return $resultJson->setData($result);
        }
    }
}
