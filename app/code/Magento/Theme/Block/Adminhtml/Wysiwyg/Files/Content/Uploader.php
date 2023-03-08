<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Content;

use Magento\Backend\Block\Media\Uploader as MediaUploader;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\File\Size;
use Magento\Theme\Helper\Storage as StorageHelper;

/**
 * Files uploader block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Uploader extends MediaUploader
{
    /**
     * Path to uploader template
     *
     * @var string
     */
    protected $_template = 'Magento_Theme::browser/content/uploader.phtml';

    /**
     * @var StorageHelper
     */
    protected $_storageHelper;

    /**
     * @param Context $context
     * @param Size $fileSize
     * @param StorageHelper $storageHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Size $fileSize,
        StorageHelper $storageHelper,
        array $data = []
    ) {
        $this->_storageHelper = $storageHelper;
        parent::__construct($context, $fileSize, $data);
    }

    /**
     * Prepare layout
     *
     * @return MediaUploader
     */
    protected function _prepareLayout()
    {
        $this->getConfig()->setUrl($this->getUrl('adminhtml/*/upload', $this->_storageHelper->getRequestParams()));
        return parent::_prepareLayout();
    }

    /**
     * Return storage helper
     *
     * @return StorageHelper
     */
    public function getHelperStorage()
    {
        return $this->_storageHelper;
    }
}
