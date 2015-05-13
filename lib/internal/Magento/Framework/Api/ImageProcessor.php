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
     * @var \Magento\Framework\Api\Data\EavImageContentInterfaceFactory
     */
    private $eavImageContentFactory;

    /**
     * @param Filesystem $fileSystem
     * @param ImageContentValidatorInterface $contentValidator
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Api\Data\EavImageContentInterfaceFactory $eavImageContentFactory
     */
    public function __construct(
        Filesystem $fileSystem,
        ImageContentValidatorInterface $contentValidator,
        DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\Data\EavImageContentInterfaceFactory $eavImageContentFactory)
    {
        $this->filesystem = $fileSystem;
        $this->contentValidator = $contentValidator;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->eavImageContentFactory = $eavImageContentFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CustomAttributesDataInterface $dataObjectWithCustomAttributes)
    {
        //Get all Image related custom attributes
        $imageDataObjects = $this->dataObjectHelper->getCustomAttributeValueByType(
            $dataObjectWithCustomAttributes->getCustomAttributes(),
            '\Magento\Framework\Api\Data\ImageContentInterface'
        );

        // Return if no images to process
        if(empty($imageDataObjects)) {
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

            $absolutePath = $tmpDirectory->getAbsolutePath() . $fileName;

            $eavImageContent = $this->eavImageContentFactory->create();
            $this->dataObjectHelper->mergeDataObjects(
                '\Magento\Framework\Api\Data\ImageContentInterface',
                $eavImageContent,
                $imageContent
            );

            $eavImageContent->setSize(@stat($imageContent->getBase64EncodedData()['size']));
            $eavImageContent->setTmpName($absolutePath);

            $dataObjectWithCustomAttributes->setCustomAttribute($imageDataObject->getAttributeCode(), $eavImageContent);
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
