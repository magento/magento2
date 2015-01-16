<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentInterface;
use Magento\Framework\Exception\InputException;

class ContentValidator
{
    /**
     * @var array
     */
    private $defaultMimeTypes = [
        'image/jpg',
        'image/jpeg',
        'image/gif',
        'image/png',
    ];

    /**
     * @var array
     */
    private $allowedMimeTypes;

    /**
     * @param array $allowedMimeTypes
     */
    public function __construct(
        array $allowedMimeTypes = []
    ) {
        $this->allowedMimeTypes = array_merge($this->defaultMimeTypes, $allowedMimeTypes);
    }

    /**
     * Check if gallery entry content is valid
     *
     * @param ProductAttributeMediaGalleryEntryContentInterface $entryContent
     * @return bool
     * @throws InputException
     */
    public function isValid(ProductAttributeMediaGalleryEntryContentInterface $entryContent)
    {
        $fileContent = @base64_decode($entryContent->getEntryData(), true);
        if (empty($fileContent)) {
            throw new InputException('The image content must be valid base64 encoded data.');
        }
        $imageProperties = @getimagesizefromstring($fileContent);
        if (empty($imageProperties)) {
            throw new InputException('The image content must be valid base64 encoded data.');
        }
        $sourceMimeType = $imageProperties['mime'];
        if ($sourceMimeType != $entryContent->getMimeType() || !$this->isMimeTypeValid($sourceMimeType)) {
            throw new InputException('The image MIME type is not valid or not supported.');
        }
        if (!$this->isNameValid($entryContent->getName())) {
            throw new InputException('Provided image name contains forbidden characters.');
        }
        return true;
    }

    /**
     * Check if given mime type is valid
     *
     * @param string $mimeType
     * @return bool
     */
    protected function isMimeTypeValid($mimeType)
    {
        return in_array($mimeType, $this->allowedMimeTypes);
    }

    /**
     * Check if given filename is valid
     *
     * @param string $name
     * @return bool
     */
    protected function isNameValid($name)
    {
        // Cannot contain \ / : * ? " < > |
        if (!preg_match('/^[^\\/?*:";<>()|{}\\\\]+$/', $name)) {
            return false;
        }
        return true;
    }
}
