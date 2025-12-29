<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Phrase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageProcessor implements ImageProcessorInterface, ImageContentUploaderInterface
{
    /**
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
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Uploader
     */
    private $uploader;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @param Filesystem $fileSystem
     * @param ImageContentValidatorInterface $contentValidator
     * @param DataObjectHelper $dataObjectHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param Uploader $uploader
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
     * @inheritdoc
     */
    public function save(
        CustomAttributesDataInterface $dataObjectWithCustomAttributes,
        $entityType,
        ?CustomAttributesDataInterface $previousCustomerData = null
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
     * @inheritdoc
     */
    public function processImageContent($entityType, $imageContent)
    {
        $tmpFileName = $this->saveToTmpDir($imageContent);

        try {
            return $this->moveFromTmpDir(
                $imageContent,
                $tmpFileName,
                $this->mediaDirectory,
                (string) $entityType,
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function saveToTmpDir(
        ImageContentInterface $imageContent,
        bool $validate = true
    ): string {
        if ($validate && !$this->contentValidator->isValid($imageContent)) {
            throw new InputException(new Phrase('The image content is invalid. Verify the content and try again.'));
        }

        $fileContent = @base64_decode($imageContent->getBase64EncodedData(), true);
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $fileName = $this->getFileName($imageContent);
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $tmpFileName = substr(md5(rand()), 0, 7) . '.' . $fileName;
        $tmpDirectory->writeFile($tmpFileName, $fileContent);

        return $tmpFileName;
    }

    /**
     * @inheritDoc
     */
    public function moveFromTmpDir(
        ImageContentInterface $imageContent,
        string $tmpFileName,
        WriteInterface $destinationDirectory,
        ?string $destinationPath = null,
        ?string $fileName = null,
        int $flags = 0
    ): ?string {
        $flags = $flags ?: (self::CASE_SENSITIVE | self::PATH_DISPERSION | self::RENAME_IF_EXIST);
        $fileName = $fileName ?? $this->getFileName($imageContent);
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $this->uploader->processFileAttributes([
            'tmp_name' => $tmpDirectory->getAbsolutePath() . $tmpFileName,
            'name' => $fileName
        ]);
        $this->uploader->setFilesDispersion((bool)($flags & self::PATH_DISPERSION));
        // setFilenamesCaseSensitivity is actually setting whether the filenames are case-insensitive,
        // meaning that passing TRUE makes the filenames case-insensitive and vice versa.
        $this->uploader->setFilenamesCaseSensitivity(!($flags & self::CASE_SENSITIVE));
        $this->uploader->setAllowRenameFiles((bool)($flags & self::RENAME_IF_EXIST));
        $this->uploader->save($this->mediaDirectory->getAbsolutePath($destinationPath), $fileName);
        return $this->uploader->getUploadedFileName();
    }

    /**
     * Get mime type extension
     *
     * @param string $mimeType
     * @return string
     */
    protected function getMimeTypeExtension($mimeType)
    {
        return $this->mimeTypeExtensionMap[$mimeType] ?? '';
    }

    /**
     * Get file name
     *
     * @param ImageContentInterface $imageContent
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
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
