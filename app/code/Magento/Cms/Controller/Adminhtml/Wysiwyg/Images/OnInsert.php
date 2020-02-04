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
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Fire when select image
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $imagesHelper = $this->_objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class);
        $request = $this->getRequest();

        $storeId = $request->getParam('store');

        $filename = $request->getParam('filename');
        $filename = $imagesHelper->idDecode($filename);

        $asIs = $request->getParam('as_is');

        $forceStaticPath = $request->getParam('force_static_path');

        $this->_objectManager->get(\Magento\Catalog\Helper\Data::class)->setStoreId($storeId);
        $imagesHelper->setStoreId($storeId);

        if ($forceStaticPath) {
            $image = parse_url($imagesHelper->getCurrentUrl() . $filename, PHP_URL_PATH);
        } else {
            $image = $imagesHelper->getImageHtmlDeclaration($filename, $asIs);
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($image);
    }
}
