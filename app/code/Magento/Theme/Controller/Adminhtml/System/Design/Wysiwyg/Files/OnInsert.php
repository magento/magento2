<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;
use Magento\Theme\Helper\Storage;

class OnInsert extends Files
{
    /**
     * Fire when select image
     *
     * @return void
     */
    public function execute()
    {
        /** @var Storage $helperStorage */
        $helperStorage = $this->_objectManager->get(Storage::class);
        $this->getResponse()->setBody($helperStorage->getRelativeUrl());
    }
}
