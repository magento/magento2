<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

/**
 * Class \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFolder
 *
 * @since 2.0.0
 */
class DeleteFolder extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     * @since 2.0.0
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     * @since 2.0.0
     */
    protected $resultRawFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Delete folder action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
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
