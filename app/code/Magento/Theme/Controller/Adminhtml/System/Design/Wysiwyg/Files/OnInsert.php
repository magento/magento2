<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

class OnInsert extends \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files
{
    /**
     * Fire when select image
     *
     * @return void
     */
    public function execute()
    {
        /** @var $helperStorage \Magento\Theme\Helper\Storage */
        $helperStorage = $this->_objectManager->get(\Magento\Theme\Helper\Storage::class);
        $this->getResponse()->setBody($helperStorage->getRelativeUrl());
    }
}
