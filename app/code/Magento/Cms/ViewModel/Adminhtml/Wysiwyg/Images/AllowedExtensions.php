<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\ViewModel\Adminhtml\Wysiwyg\Images;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Provides the file extensions the media storage accepts for upload.
 */
class AllowedExtensions implements ArgumentInterface
{
    /**
     * @param Storage $imagesStorage
     * @param RequestInterface $request
     * @param Json $serializer
     */
    public function __construct(
        private readonly Storage $imagesStorage,
        private readonly RequestInterface $request,
        private readonly Json $serializer
    ) {
    }

    /**
     * Get JSON-encoded list of extensions allowed for the media type, falling back to the "type" request parameter
     *
     * @param string|null $mediaType
     * @return string
     */
    public function getAllowedExtensionsJson(?string $mediaType = null): string
    {
        if ($mediaType === null) {
            $requestType = $this->request->getParam('type');
            $mediaType = is_string($requestType) ? $requestType : null;
        }

        return $this->serializer->serialize($this->imagesStorage->getAllowedExtensions($mediaType));
    }
}
