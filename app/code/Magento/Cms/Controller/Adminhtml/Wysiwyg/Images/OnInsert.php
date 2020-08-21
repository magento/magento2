<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

class OnInsert extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Images\GetInsertImageContent
     */
    protected $getInsertImageContent;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Cms\Model\Wysiwyg\Images\GetInsertImageContent $getInsertImageContent
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        ?\Magento\Cms\Model\Wysiwyg\Images\GetInsertImageContent $getInsertImageContent = null
    ) {
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context, $coreRegistry);
        $this->getInsertImageContent = $getInsertImageContent ?: $this->_objectManager
            ->get('Magento\Cms\Model\Wysiwyg\Images\GetInsertImageContent');
    }

    /**
     * Fire when select image
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents(
            $this->getInsertImageContent->execute(
                $data['filename'],
                (int)$data['store_id'],
                $data['force_static_path'],
                $data['as_is']
            )
        );
    }
}
