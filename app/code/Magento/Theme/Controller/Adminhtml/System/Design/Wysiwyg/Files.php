<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Files controller
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Theme\Helper\Storage as ThemeStorageHelper;
use Magento\Theme\Model\Wysiwyg\Storage;

abstract class Files extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Theme::theme';

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param ThemeStorageHelper $storage
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        protected readonly ThemeStorageHelper $storage
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Get storage
     *
     * @return Storage
     */
    protected function _getStorage()
    {
        return $this->_objectManager->get(Storage::class);
    }
}
