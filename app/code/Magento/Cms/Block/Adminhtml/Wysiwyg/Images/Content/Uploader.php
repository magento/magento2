<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Content;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Uploader block for Wysiwyg Images
 *
 * @api
 * @since 100.0.2
 */
class Uploader extends \Magento\Backend\Block\Media\Uploader
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage
     */
    protected $_imagesStorage;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage $imagesStorage
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Cms\Model\Wysiwyg\Images\Storage $imagesStorage,
        array $data = [],
        ?Json $serializer = null
    ) {
        $this->_imagesStorage = $imagesStorage;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context, $fileSize, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $type = $this->_getMediaType();
        $allowed = $this->_imagesStorage->getAllowedExtensions($type);
        $labels = [];
        $files = [];
        foreach ($allowed as $ext) {
            $labels[] = '.' . $ext;
            $files[] = '*.' . $ext;
        }
        $this->getConfig()->setUrl(
            $this->_urlBuilder->getUrl('cms/*/upload', ['type' => $type])
        )->setFileField(
            'image'
        )->setFilters(
            ['images' => ['label' => __('Images (%1)', implode(', ', $labels)), 'files' => $files]]
        );
    }

    /**
     * Get list of file extensions allowed to upload for the current media type
     *
     * @return string[]
     */
    public function getAllowedExtensions(): array
    {
        return $this->_imagesStorage->getAllowedExtensions($this->_getMediaType());
    }

    /**
     * Get JSON-encoded list of file extensions allowed to upload for the current media type
     *
     * @return string
     */
    public function getAllowedExtensionsJson(): string
    {
        return $this->serializer->serialize($this->getAllowedExtensions());
    }

    /**
     * Return current media type based on request or data
     *
     * @return string
     */
    protected function _getMediaType()
    {
        if ($this->hasData('media_type')) {
            return $this->_getData('media_type');
        }
        return $this->getRequest()->getParam('type');
    }
}
