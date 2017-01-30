<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Files controller
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg;

abstract class Files extends \Magento\Backend\App\Action
{
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
        return $this->_objectManager->get('Magento\Theme\Model\Wysiwyg\Storage');
    }
}
