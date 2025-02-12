<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Block\Media;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Image\Adapter\UploadConfigInterface;
use Magento\Backend\Model\Image\UploadResizeConfigInterface;

/**
 * Adminhtml media library uploader
 * @api
 * @since 100.0.2
 */
class Uploader extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_config;

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::media/uploader.phtml';

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $_fileSizeService;

    /**
     * @var Json
     */
    private $jsonEncoder;

    /**
     * @var UploadResizeConfigInterface
     */
    private $imageUploadConfig;

    /**
     * @var UploadConfigInterface
     * @deprecated 101.0.1
     * @see \Magento\Backend\Model\Image\UploadResizeConfigInterface
     */
    private $imageConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\File\Size $fileSize
     * @param array $data
     * @param Json|null $jsonEncoder
     * @param UploadConfigInterface|null $imageConfig
     * @param UploadResizeConfigInterface|null $imageUploadConfig
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\File\Size $fileSize,
        array $data = [],
        ?Json $jsonEncoder = null,
        ?UploadConfigInterface $imageConfig = null,
        ?UploadResizeConfigInterface $imageUploadConfig = null
    ) {
        $this->_fileSizeService = $fileSize;
        $this->jsonEncoder = $jsonEncoder ?: ObjectManager::getInstance()->get(Json::class);
        $this->imageConfig = $imageConfig
            ?: ObjectManager::getInstance()->get(UploadConfigInterface::class);
        $this->imageUploadConfig = $imageUploadConfig
            ?: ObjectManager::getInstance()->get(UploadResizeConfigInterface::class);
        parent::__construct($context, $data);
    }

    /**
     * Initialize block.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId($this->getId() . '_Uploader');

        $uploadUrl = $this->_urlBuilder->getUrl('adminhtml/*/upload');
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
     */
    public function getFileSizeService()
    {
        return $this->_fileSizeService;
    }

    /**
     * Get Image Upload Maximum Width Config.
     *
     * @return int
     * @since 100.2.7
     */
    public function getImageUploadMaxWidth()
    {
        return $this->imageUploadConfig->getMaxWidth();
    }

    /**
     * Get Image Upload Maximum Height Config.
     *
     * @return int
     * @since 100.2.7
     */
    public function getImageUploadMaxHeight()
    {
        return $this->imageUploadConfig->getMaxHeight();
    }

    /**
     * Prepares layout and set element renderer
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->addPageAsset('jquery/uppy/dist/uppy.fileupload-ui.css');
        return parent::_prepareLayout();
    }

    /**
     * Retrieve uploader js object name
     *
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * Retrieve config json
     *
     * @return string
     */
    public function getConfigJson()
    {
        return $this->jsonEncoder->encode($this->getConfig()->getData());
    }

    /**
     * Retrieve config object
     *
     * @return \Magento\Framework\DataObject
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
     */
    public function getUploaderUrl($url)
    {
        return $this->_assetRepo->getUrl($url);
    }
}
