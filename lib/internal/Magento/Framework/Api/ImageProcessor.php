<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Filesystem;

/**
 * Class ImageProcessor
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
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param Filesystem $fileSystem
     * @param ImageContentValidatorInterface $contentValidator
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Filesystem $fileSystem,
        ImageContentValidatorInterface $contentValidator,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->filesystem = $fileSystem;
        $this->contentValidator = $contentValidator;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CustomAttributesDataInterface $dataObjectWithCustomAttributes, $entityType)
    {
        //Get all Image related custom attributes
        $imageDataObjects = $this->dataObjectHelper->getCustomAttributeValueByType(
            $dataObjectWithCustomAttributes->getCustomAttributes(),
            '\Magento\Framework\Api\Data\ImageContentInterface'
        );

        // Return if no images to process
        if (empty($imageDataObjects)) {
            return $dataObjectWithCustomAttributes;
        }

        // For every image, save it and replace it with corresponding Eav data object
        /** @var $imageDataObject \Magento\Framework\Api\AttributeValue */
        foreach ($imageDataObjects as $imageDataObject) {

            /** @var $imageContent \Magento\Framework\Api\Data\ImageContentInterface */
            $imageContent = $imageDataObject->getValue();

            if (!$this->contentValidator->isValid($imageContent)) {
                throw new InputException(__('The image content is not valid.'));
            }

            $fileContent = @base64_decode($imageContent->getBase64EncodedData(), true);
            $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
            $fileName = substr(md5(rand()), 0, 7) . '.' . $imageContent->getName();
            $tmpDirectory->writeFile($fileName, $fileContent);

            $fileAttributes = [
                'tmp_name' => $tmpDirectory->getAbsolutePath() . $fileName,
                'name' => $imageContent->getName()
            ];

            try {
                $uploader = new \Magento\Framework\Api\Uploader($fileAttributes);
                $uploader->setFilesDispersion(true);
                $uploader->setFilenamesCaseSensitivity(false);
                $uploader->setAllowRenameFiles(true);
                $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                $destinationFolder = $entityType;
                $uploader->save($mediaDirectory->getAbsolutePath($destinationFolder), $imageContent->getName());
                //Set filename from static media location into data object
                $dataObjectWithCustomAttributes->setCustomAttribute(
                    $imageDataObject->getAttributeCode(),
                    $uploader->getUploadedFileName()
                );
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }

        return $dataObjectWithCustomAttributes;
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
