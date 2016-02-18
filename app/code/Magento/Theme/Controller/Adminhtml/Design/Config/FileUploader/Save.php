<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\Design\Config\FileUploader;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Theme\Model\Design\Config\FileUploader\ImageProcessor;

class Save extends Action
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var ImageProcessor
     */
    protected $imageProcessor;

    /**
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param ImageProcessor $imageProcessor
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        ImageProcessor $imageProcessor
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result = $this->imageProcessor->saveToTmp(key($_FILES));
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
