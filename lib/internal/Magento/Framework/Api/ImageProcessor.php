<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Filesystem;

/**
 * Class ImageProcessor
 *
 * @api
 */
class ImageProcessor implements ImageProcessorInterface
{
    /**
     * MIME type/extension map
     *
     * @var array
     */
    protected $mimeTypeExtensionMap = [
        'image/jpg' => 'jpg',
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
        'image/png' => 'png',
    ];

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Filesystem
     */
    private $contentValidator;

    /**
     * @param Filesystem $fileSystem
     * @param ImageContentValidatorInterface $contentValidator
     */
    public function __construct(Filesystem $fileSystem, ImageContentValidatorInterface $contentValidator)
    {
        $this->filesystem = $fileSystem;
        $this->contentValidator = $contentValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ImageContentInterface $imageContent)
    {
        if (!$this->contentValidator->isValid($imageContent)) {
            throw new InputException(__('The image content is not valid.'));
        }

        $fileContent = @base64_decode($imageContent->getBase64EncodedData(), true);
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);

        $fileName =  substr(md5(rand()), 0, 7) . '.' . $imageContent->getName();
        $tmpDirectory->writeFile($fileName, $fileContent);

        $absolutePath = $tmpDirectory->getAbsolutePath() . $fileName;
        return $absolutePath;
    }

    /**
     * @param string $mimeType
     * @return string
     */
    protected function getMimeTypeExtension($mimeType)
    {
        if (isset($this->mimeTypeExtensionMap[$mimeType])) {
            return $this->mimeTypeExtensionMap[$mimeType];
        } else {
            return "";
        }
    }
}
