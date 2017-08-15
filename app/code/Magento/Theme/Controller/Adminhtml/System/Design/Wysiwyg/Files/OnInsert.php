<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

/**
 * Class \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\OnInsert
 *
 */
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
