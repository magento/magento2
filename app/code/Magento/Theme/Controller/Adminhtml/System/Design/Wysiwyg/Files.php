<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Files controller
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg;

abstract class Files extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Theme::theme';

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Theme\Helper\Storage
     */
    protected $storage;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Theme\Helper\Storage $storage
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Theme\Helper\Storage $storage
    ) {
        $this->_fileFactory = $fileFactory;
        $this->storage = $storage;
        parent::__construct($context);
    }

    /**
     * Get storage
     *
     * @return \Magento\Theme\Model\Wysiwyg\Storage
     */
    protected function _getStorage()
    {
        return $this->_objectManager->get(\Magento\Theme\Model\Wysiwyg\Storage::class);
    }
}
