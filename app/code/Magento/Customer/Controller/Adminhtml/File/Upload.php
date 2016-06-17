<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\File;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\FileProcessor;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Upload extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var FileProcessor
     */
    private $fileProcessor;

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadataService;

    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadataService;

    /**
     * @var ElementFactory
     */
    private $elementFactory;

    /**
     * @param Context $context
     * @param FileProcessor $fileProcessor
     * @param CustomerMetadataInterface $customerMetadataService
     * @param ElementFactory $elementFactory
     */
    public function __construct(
        Context $context,
        FileProcessor $fileProcessor,
        CustomerMetadataInterface $customerMetadataService,
        AddressMetadataInterface $addressMetadataService,
        ElementFactory $elementFactory
    ) {
        $this->fileProcessor = $fileProcessor;
        $this->customerMetadataService = $customerMetadataService;
        $this->addressMetadataService = $addressMetadataService;
        $this->elementFactory = $elementFactory;
        parent::__construct($context);
    }
    
    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            if (empty($_FILES)) {
                throw new LocalizedException(__('$_FILES array is empty.'));
            }

            $scope = key($_FILES);

            $this->fileProcessor->setEntityType($this->getEntityType());

            // Must be executed before any operations with $_FILES!
            $this->convertFilesArray($scope);

            $attributeCode = key($_FILES[$scope]['name']);
            $attributeData = $this->getData($scope, $attributeCode);

            $attributeMetadata = $this->getAttributeMetadata($attributeCode);
            $this->validateAttributeData($attributeMetadata, $attributeData);
            $this->overrideAllowedExtensions($attributeMetadata);

            $result = $this->fileProcessor->saveTemporaryFile($scope . '[' . $attributeCode . ']');
            if (!is_array($result)) {
                throw new LocalizedException(__('Something went wrong while saving file.'));
            }

            // Update tmp_name param. Required for attribute validation!
            $result['tmp_name'] = $result['path'] . '/' . ltrim($result['file'], '/');

            $result['url'] = $this->fileProcessor->getViewUrl(
                FileProcessor::TMP_DIR . '/' . ltrim($result['name'], '/'),
                $attributeMetadata->getFrontendInput()
            );
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }

    /**
     * Validate uploaded file
     *
     * @param AttributeMetadataInterface $attributeMetadata
     * @param string $attributeData
     * @return void
     * @throws \Exception
     */
    private function validateAttributeData(AttributeMetadataInterface $attributeMetadata, $attributeData)
    {
        $formElement = $this->elementFactory->create(
            $attributeMetadata,
            null,
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER
        );

        $errors = $formElement->validateValue($attributeData);
        if (true !== $errors) {
            $errorMessage = implode('</br>', $errors);
            throw new \Exception($errorMessage);
        }
    }

    /**
     * Retrieve data from global $_FILES array
     *
     * @param string $scope
     * @param string $attributeCode
     * @return array
     */
    private function getData($scope, $attributeCode)
    {
        $result = [];

        $fileAttributes = $_FILES[$scope];
        foreach ($fileAttributes as $attributeName => $attributeValue) {
            $result[$attributeName] = $attributeValue[$attributeCode];
        }

        return $result;
    }

    /**
     * Override allowed extensions
     *
     * @param AttributeMetadataInterface $attributeMetadata
     */
    private function overrideAllowedExtensions(AttributeMetadataInterface $attributeMetadata)
    {
        $validationRules = $attributeMetadata->getValidationRules();
        foreach ($validationRules as $validationRule) {
            if ($validationRule->getName() == 'file_extensions') {
                $allowedExtensions = explode(',', $validationRule->getValue());
                array_walk($allowedExtensions, function (&$value) {
                    $value = strtolower(trim($value));
                });
                $this->fileProcessor->setAllowedExtensions($allowedExtensions);
            }
        }
    }

    /**
     * Update global $_FILES array. Convert data to standard form
     *
     * NOTE: This conversion is required to use \Magento\Framework\File\Uploader::_setUploadFileId($fileId) method.
     *
     * @param string $scope
     * @return void
     */
    private function convertFilesArray($scope)
    {
        foreach($_FILES[$scope] as $itemKey => $item) {
            foreach ($item as $value) {
                if (is_array($value)) {
                    $_FILES[$scope][$itemKey] = [
                        key($value) => current($value),
                    ];
                }
            }
        }
    }

    /**
     * Get attribute metadata
     *
     * @param string $attributeCode
     * @return AttributeMetadataInterface
     * @throws NoSuchEntityException
     */
    private function getAttributeMetadata($attributeCode)
    {
        $entityType = $this->getEntityType();

        if ($entityType == CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
            $attributeMetadata = $this->customerMetadataService->getAttributeMetadata($attributeCode);
            return $attributeMetadata;
        }

        if ($entityType == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
            $attributeMetadata = $this->addressMetadataService->getAttributeMetadata($attributeCode);
            return $attributeMetadata;
        }

        throw NoSuchEntityException::singleField('entityType', $entityType);
    }

    /**
     * Get entity type
     *
     * @return string
     */
    private function getEntityType()
    {
        $entityType = key($_FILES);
        if ($entityType == 'address') {
            $entityType = AddressMetadataInterface::ENTITY_TYPE_ADDRESS;
        }
        return $entityType;
    }
}
