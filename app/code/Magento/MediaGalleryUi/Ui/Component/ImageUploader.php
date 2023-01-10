<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Ui\Component;

use Magento\Framework\File\Size;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Container;

/**
 * Image Uploader component
 */
class ImageUploader extends Container
{
    private const ACCEPT_FILE_TYPES = '/(\.|\/)(gif|jpe?g|png)$/i';
    private const ALLOWED_EXTENSIONS = 'jpg jpeg png gif';

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Size
     */
    private $size;

    /**
     * @param Size $size
     * @param ContextInterface $context
     * @param UrlInterface $url
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Size $size,
        ContextInterface $context,
        UrlInterface $url,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->size = $size;
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function prepare(): void
    {
        parent::prepare();
        $this->setData(
            'config',
            array_replace_recursive(
                (array) $this->getData('config'),
                [
                    'imageUploadUrl' => $this->url->getUrl('media_gallery/image/upload', ['type' => 'image']),
                    'acceptFileTypes' => self::ACCEPT_FILE_TYPES,
                    'allowedExtensions' => self::ALLOWED_EXTENSIONS,
                    'maxFileSize' => $this->size->getMaxFileSize()
                ]
            )
        );
    }
}
