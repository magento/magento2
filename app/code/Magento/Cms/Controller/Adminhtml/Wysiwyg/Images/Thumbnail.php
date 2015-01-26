<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Backend\App\Action;

class Thumbnail extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\LayoutFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context, $coreRegistry);
    }


    /**
     * Generate image thumbnail on the fly
     *
     * @return void
     */
    public function execute()
    {
        $file = $this->getRequest()->getParam('file');
        $file = $this->_objectManager->get('Magento\Cms\Helper\Wysiwyg\Images')->idDecode($file);
        $thumb = $this->getStorage()->resizeOnTheFly($file);
        $resultLayout = $this->resultLayoutFactory->create();
        if ($thumb !== false) {
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $image */
            $image = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
            $image->open($thumb);
            $resultLayout->setHeader('Content-Type', $image->getMimeType())->setBody($image->getImage());
        } else {
            // todo: generate some placeholder
        }
    }
}
