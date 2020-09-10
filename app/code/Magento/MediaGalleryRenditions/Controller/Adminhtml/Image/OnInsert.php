<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaGalleryRenditions\Controller\Adminhtml\Image;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\Wysiwyg\Images\GetInsertImageContent;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class OnInsert
 */
class OnInsert extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var GetInsertImageContent
     */
    private $getInsertImageContent;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param GetInsertImageContent|null $getInsertImageContent
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        GetInsertImageContent $getInsertImageContent
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->getInsertImageContent = $getInsertImageContent;
    }

    /**
     * Return a content (just a link or an html block) for inserting image to the content
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $path = $this->getInsertImageContent->execute(
            $data['filename'],
            $data['force_static_path'],
            $data['as_is'],
            isset($data['store']) ? (int)$data['store'] : null
        );
        $size = $this->getInsertImageContent->getImageSize($path);
        $type = $this->getInsertImageContent->getMimeType($path);
        return $this->resultJsonFactory->create()->setData(['path' => $path, 'size' => $size, 'type' => $type]);
    }
}
