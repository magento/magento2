<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Backend\App\Action\Context;
use Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;
use Magento\Cms\Model\Wysiwyg\Images\GetInsertImageContent;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;

class OnInsert extends Images implements HttpPostActionInterface
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var GetInsertImageContent
     */
    private $getInsertImageContent;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param RawFactory $resultRawFactory
     * @param GetInsertImageContent $getInsertImageContent
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        RawFactory $resultRawFactory,
        ?GetInsertImageContent $getInsertImageContent = null
    ) {
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context, $coreRegistry);
        $this->getInsertImageContent = $getInsertImageContent ?: $this->_objectManager
            ->get(GetInsertImageContent::class);
    }

    /**
     * Return a content (just a link or an html block) for inserting image to the content
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        return $this->resultRawFactory->create()->setContents(
            $this->getInsertImageContent->execute(
                $data['filename'],
                $data['force_static_path'],
                $data['as_is'],
                isset($data['store']) ? (int) $data['store'] : null
            )
        );
    }
}
