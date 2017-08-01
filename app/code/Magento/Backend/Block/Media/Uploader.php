<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Media;

/**
 * Adminhtml media library uploader
 * @api
 * @since 2.0.0
 */
class Uploader extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Framework\DataObject
     * @since 2.0.0
     */
    protected $_config;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Backend::media/uploader.phtml';

    /**
     * @var \Magento\Framework\File\Size
     * @since 2.0.0
     */
    protected $_fileSizeService;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\File\Size $fileSize
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\File\Size $fileSize,
        array $data = []
    ) {
        $this->_fileSizeService = $fileSize;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId($this->getId() . '_Uploader');

        $uploadUrl = $this->_urlBuilder->addSessionParam()->getUrl('adminhtml/*/upload');
        $this->getConfig()->setUrl($uploadUrl);
        $this->getConfig()->setParams(['form_key' => $this->getFormKey()]);
        $this->getConfig()->setFileField('file');
        $this->getConfig()->setFilters(
            [
                'images' => [
                    'label' => __('Images (.gif, .jpg, .png)'),
                    'files' => ['*.gif', '*.jpg', '*.png'],
                ],
                'media' => [
                    'label' => __('Media (.avi, .flv, .swf)'),
                    'files' => ['*.avi', '*.flv', '*.swf'],
                ],
                'all' => ['label' => __('All Files'), 'files' => ['*.*']],
            ]
        );
    }

    /**
     * Get file size
     *
     * @return \Magento\Framework\File\Size
     * @since 2.0.0
     */
    public function getFileSizeService()
    {
        return $this->_fileSizeService;
    }

    /**
     * Prepares layout and set element renderer
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->addPageAsset('jquery/fileUploader/css/jquery.fileupload-ui.css');
        return parent::_prepareLayout();
    }

    /**
     * Retrieve uploader js object name
     *
     * @return string
     * @since 2.0.0
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * Retrieve config json
     *
     * @return string
     * @since 2.0.0
     */
    public function getConfigJson()
    {
        return $this->_coreData->jsonEncode($this->getConfig()->getData());
    }

    /**
     * Retrieve config object
     *
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function getConfig()
    {
        if (null === $this->_config) {
            $this->_config = new \Magento\Framework\DataObject();
        }

        return $this->_config;
    }

    /**
     * Retrieve full uploader SWF's file URL
     * Implemented to solve problem with cross domain SWFs
     * Now uploader can be only in the same URL where backend located
     *
     * @param string $url url to uploader in current theme
     * @return string full URL
     * @since 2.0.0
     */
    public function getUploaderUrl($url)
    {
        return $this->_assetRepo->getUrl($url);
    }
}
