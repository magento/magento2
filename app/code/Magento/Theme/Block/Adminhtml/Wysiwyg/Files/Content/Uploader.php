<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Content;

/**
 * Files uploader block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Uploader extends \Magento\Backend\Block\Media\Uploader
{
    /**
     * Path to uploader template
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'browser/content/uploader.phtml';

    /**
     * @var \Magento\Theme\Helper\Storage
     * @since 2.0.0
     */
    protected $_storageHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magento\Theme\Helper\Storage $storageHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Theme\Helper\Storage $storageHelper,
        array $data = []
    ) {
        $this->_storageHelper = $storageHelper;
        parent::__construct($context, $fileSize, $data);
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Backend\Block\Media\Uploader
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->getConfig()->setUrl($this->getUrl('adminhtml/*/upload', $this->_storageHelper->getRequestParams()));
        return parent::_prepareLayout();
    }

    /**
     * Return storage helper
     *
     * @return \Magento\Theme\Helper\Storage
     * @since 2.0.0
     */
    public function getHelperStorage()
    {
        return $this->_storageHelper;
    }
}
