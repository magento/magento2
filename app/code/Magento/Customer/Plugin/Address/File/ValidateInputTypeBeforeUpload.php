<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Plugin\Address\File;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Controller\Address\File\Upload;
use Magento\Framework\Exception\LocalizedException;

class ValidateInputTypeBeforeUpload
{
    /**
     * Input type file to validate
     */
    private const INPUT_TYPE = 'file';

    /**
     * @var AddressMetadataInterface
     */
    private AddressMetadataInterface $addressMetadataService;

    /**
     * validateInputTypeBeforeUpload constructor.
     *
     * @param AddressMetadataInterface $addressMetadataService
     */
    public function __construct(
        AddressMetadataInterface $addressMetadataService
    ) {
        $this->addressMetadataService = $addressMetadataService;
    }

    /**
     * Before executing the upload file action, validate that the attribute is a file input type.
     *
     * @param Upload $subject
     * @return void
     * @throws LocalizedException
     */
    public function beforeExecute(Upload $subject): void
    {
        $requestedFiles = $subject->getRequest()->getFiles('custom_attributes');
        if (!empty($requestedFiles)) {
            $attributeCode = key($requestedFiles);
            $attributeMetadata = $this->addressMetadataService->getAttributeMetadata($attributeCode);
            if ($attributeMetadata->getFrontendInput() !== self::INPUT_TYPE) {
                throw new LocalizedException(
                    __('Attribute with code %1 is not a file input type.', $attributeCode)
                );
            }
        }
    }
}
