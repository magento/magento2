<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Backend\App\Action;

/**
 * Class \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\Thumbnail
 *
 * @since 2.0.0
 */
class Thumbnail extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     * @since 2.0.0
     */
    protected $resultRawFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @since 2.0.0
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Generate image thumbnail on the fly
     *
     * @return \Magento\Framework\Controller\Result\Raw
     * @since 2.0.0
     */
    public function execute()
    {
        $file = $this->getRequest()->getParam('file');
        $file = $this->_objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class)->idDecode($file);
        $thumb = $this->getStorage()->resizeOnTheFly($file);
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        if ($thumb !== false) {
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $image */
            $image = $this->_objectManager->get(\Magento\Framework\Image\AdapterFactory::class)->create();
            $image->open($thumb);
            $resultRaw->setHeader('Content-Type', $image->getMimeType());
            $resultRaw->setContents($image->getImage());
            return $resultRaw;
        } else {
            // todo: generate some placeholder
        }
    }
}
