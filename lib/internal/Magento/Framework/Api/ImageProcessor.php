<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Filesystem;
use Magento\Framework\Phrase;

/**
 * Class ImageProcessor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class ImageProcessor implements ImageProcessorInterface
{
    /**
     * MIME type/extension map
     *
     * @var array
     * @since 2.0.0
     */
    protected $mimeTypeExtensionMap = [
        'image/jpg' => 'jpg',
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
        'image/png' => 'png',
    ];

    /**
     * @var Filesystem
     * @since 2.0.0
     */
    private $filesystem;

    /**
     * @var Filesystem
     * @since 2.0.0
     */
    private $contentValidator;

    /**
     * @var DataObjectHelper
     * @since 2.0.0
     */
    private $dataObjectHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @var Uploader
     * @since 2.0.0
     */
    private $uploader;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     * @since 2.0.0
     */
    private $mediaDirectory;

    /**
     * @param Filesystem $fileSystem
     * @param ImageContentValidatorInterface $contentValidator
     * @param DataObjectHelper $dataObjectHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param Uploader $uploader
     * @since 2.0.0
     */
    public function __construct(
        Filesystem $fileSystem,
        ImageContentValidatorInterface $contentValidator,
        DataObjectHelper $dataObjectHelper,
        \Psr\Log\LoggerInterface $logger,
        Uploader $uploader
    ) {
        $this->filesystem = $fileSystem;
        $this->contentValidator = $contentValidator;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logger = $logger;
        $this->uploader = $uploader;
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function save(
        CustomAttributesDataInterface $dataObjectWithCustomAttributes,
        $entityType,
        CustomAttributesDataInterface $previousCustomerData = null
    ) {
        //Get all Image related custom attributes
        $imageDataObjects = $this->dataObjectHelper->getCustomAttributeValueByType(
            $dataObjectWithCustomAttributes->getCustomAttributes(),
            \Magento\Framework\Api\Data\ImageContentInterface::class
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

            $filename = $this->processImageContent($entityType, $imageContent);

            //Set filename from static media location into data object
            $dataObjectWithCustomAttributes->setCustomAttribute(
                $imageDataObject->getAttributeCode(),
                $filename
            );

            //Delete previously saved image if it exists
            if ($previousCustomerData) {
                $previousImageAttribute = $previousCustomerData->getCustomAttribute(
                    $imageDataObject->getAttributeCode()
                );
                if ($previousImageAttribute) {
                    $previousImagePath = $previousImageAttribute->getValue();
                    if (!empty($previousImagePath) && ($previousImagePath != $filename)) {
                        @unlink($this->mediaDirectory->getAbsolutePath() . $entityType . $previousImagePath);
                    }
                }
            }
        }

        return $dataObjectWithCustomAttributes;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function processImageContent($entityType, $imageContent)
    {
        if (!$this->contentValidator->isValid($imageContent)) {
            throw new InputException(new Phrase('The image content is not valid.'));
        }

        $fileContent = @base64_decode($imageContent->getBase64EncodedData(), true);
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $fileName = $this->getFileName($imageContent);
        $tmpFileName = substr(md5(rand()), 0, 7) . '.' . $fileName;
        $tmpDirectory->writeFile($tmpFileName, $fileContent);

        $fileAttributes = [
            'tmp_name' => $tmpDirectory->getAbsolutePath() . $tmpFileName,
            'name' => $imageContent->getName()
        ];

        try {
            $this->uploader->processFileAttributes($fileAttributes);
            $this->uploader->setFilesDispersion(true);
            $this->uploader->setFilenamesCaseSensitivity(false);
            $this->uploader->setAllowRenameFiles(true);
            $destinationFolder = $entityType;
            $this->uploader->save($this->mediaDirectory->getAbsolutePath($destinationFolder), $fileName);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $this->uploader->getUploadedFileName();
    }

    /**
     * @param string $mimeType
     * @return string
     * @since 2.0.0
     */
    protected function getMimeTypeExtension($mimeType)
    {
        return isset($this->mimeTypeExtensionMap[$mimeType]) ? $this->mimeTypeExtensionMap[$mimeType] : '';
    }

    /**
     * @param ImageContentInterface $imageContent
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.1.0
     */
    private function getFileName($imageContent)
    {
        $fileName = $imageContent->getName();
        if (!pathinfo($fileName, PATHINFO_EXTENSION)) {
            if (!$imageContent->getType() || !$this->getMimeTypeExtension($imageContent->getType())) {
                throw new InputException(new Phrase('Cannot recognize image extension.'));
            }
            $fileName .= '.' . $this->getMimeTypeExtension($imageContent->getType());
        }
        return $fileName;
    }
}
