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
 * @since 2.0.0
 */
class OnInsert extends \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files
{
    /**
     * Fire when select image
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var $helperStorage \Magento\Theme\Helper\Storage */
        $helperStorage = $this->_objectManager->get(\Magento\Theme\Helper\Storage::class);
        $this->getResponse()->setBody($helperStorage->getRelativeUrl());
    }
}
