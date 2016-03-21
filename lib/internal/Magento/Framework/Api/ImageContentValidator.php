<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Phrase;

/**
 * Class for Image content validation
 */
class ImageContentValidator implements ImageContentValidatorInterface
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
     * @param ImageContentInterface $imageContent
     * @return bool
     * @throws InputException
     */
    public function isValid(ImageContentInterface $imageContent)
    {
        $fileContent = @base64_decode($imageContent->getBase64EncodedData(), true);
        if (empty($fileContent)) {
            throw new InputException(new Phrase('The image content must be valid base64 encoded data.'));
        }
        $imageProperties = @getimagesizefromstring($fileContent);
        if (empty($imageProperties)) {
            throw new InputException(new Phrase('The image content must be valid base64 encoded data.'));
        }
        $sourceMimeType = $imageProperties['mime'];
        if ($sourceMimeType != $imageContent->getType() || !$this->isMimeTypeValid($sourceMimeType)) {
            throw new InputException(new Phrase('The image MIME type is not valid or not supported.'));
        }
        if (!$this->isNameValid($imageContent->getName())) {
            throw new InputException(new Phrase('Provided image name contains forbidden characters.'));
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
