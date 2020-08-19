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
     * @var \Magento\Cms\Model\Wysiwyg\Images\PrepareImage
     */
    protected $prepareImage;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Cms\Model\Wysiwyg\Images\PrepareImage $prepareImage
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Cms\Model\Wysiwyg\Images\PrepareImage $prepareImage
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->prepareImage = $prepareImage;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Fire when select image
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();

        /** @var \Magento\Cms\Model\Wysiwyg\Images\PrepareImage $image */
        $image = $this->prepareImage->execute($request->getParams());

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($image);
    }
}
