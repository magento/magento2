<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Files controller
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg;

/**
 * Class \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files
 *
 * @since 2.0.0
 */
abstract class Files extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     * @since 2.0.0
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Theme\Helper\Storage
     * @since 2.0.0
     */
    protected $storage;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Theme\Helper\Storage $storage
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _getStorage()
    {
        return $this->_objectManager->get(\Magento\Theme\Model\Wysiwyg\Storage::class);
    }
}
