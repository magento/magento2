<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\Design\Config\FileUploader;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Theme\Model\Design\Config\FileUploader\FileProcessor;

/**
 * File Uploads Action Controller
 *
 * @api
 * @since 100.1.0
 */
class Save extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Magento_Theme::theme';

    /**
     * @param Context $context
     * @param FileProcessor $fileProcessor
     */
    public function __construct(
        Context $context,
        protected readonly FileProcessor $fileProcessor
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     * @since 100.1.0
     */
    public function execute()
    {
        $result = $this->fileProcessor->saveToTmp(key($_FILES));
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
