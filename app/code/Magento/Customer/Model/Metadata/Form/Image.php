<?php
/**
 * Form Element Image Data Model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\FileProcessor;
use Magento\Framework\Api\ArrayObjectSearch;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\App\ObjectManager;

class Image extends File
{
    /**
     * @var ImageContentInterfaceFactory
     */
    private $imageContentFactory;

    /**
     * Validate file by attribute validate rules
     * Return array of errors
     *
     * @param array $value
     * @return string[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _validateByRules($value)
    {
        $label = $value['name'];
        $rules = $this->getAttribute()->getValidationRules();

        $imageProp = @getimagesize($value['tmp_name']);

        if (!$this->_isUploadedFile($value['tmp_name']) || !$imageProp) {
            return [__('"%1" is not a valid file.', $label)];
        }

        $allowImageTypes = [1 => 'gif', 2 => 'jpg', 3 => 'png'];

        if (!isset($allowImageTypes[$imageProp[2]])) {
            return [__('"%1" is not a valid image format.', $label)];
        }

        // modify image name
        $extension = pathinfo($value['name'], PATHINFO_EXTENSION);
        if ($extension != $allowImageTypes[$imageProp[2]]) {
            $value['name'] = pathinfo($value['name'], PATHINFO_FILENAME) . '.' . $allowImageTypes[$imageProp[2]];
        }

        $maxFileSize = ArrayObjectSearch::getArrayElementByName(
            $rules,
            'max_file_size'
        );
        $errors = [];
        if ($maxFileSize !== null) {
            $size = $value['size'];
            if ($maxFileSize < $size) {
                $errors[] = __('"%1" exceeds the allowed file size.', $label);
            }
        }

        $maxImageWidth = ArrayObjectSearch::getArrayElementByName(
            $rules,
            'max_image_width'
        );
        if ($maxImageWidth !== null) {
            if ($maxImageWidth < $imageProp[0]) {
                $r = $maxImageWidth;
                $errors[] = __('"%1" width exceeds allowed value of %2 px.', $label, $r);
            }
        }

        $maxImageHeight = ArrayObjectSearch::getArrayElementByName(
            $rules,
            'max_image_heght'
        );
        if ($maxImageHeight !== null) {
            if ($maxImageHeight < $imageProp[1]) {
                $r = $maxImageHeight;
                $errors[] = __('"%1" height exceeds allowed value of %2 px.', $label, $r);
            }
        }

        return $errors;
    }

    /**
     * Process file uploader UI component data
     *
     * @param array $value
     * @return bool|int|ImageContentInterface|string
     */
    protected function processUiComponentValue(array $value)
    {
        if ($this->_entityTypeCode == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
            $result = $this->processCustomerAddressValue($value);
            return $result;
        }

        if ($this->_entityTypeCode == CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
            $result = $this->processCustomerValue($value);
            return $result;
        }

        return $this->_value;
    }

    /**
     * Process file uploader UI component data for customer_address entity
     *
     * @param array $value
     * @return string
     */
    protected function processCustomerAddressValue(array $value)
    {
        $result = $this->getFileProcessor()->moveTemporaryFile($value['file']);
        return $result;
    }

    /**
     * Process file uploader UI component data for customer entity
     *
     * @param array $value
     * @return bool|int|ImageContentInterface|string
     */
    protected function processCustomerValue(array $value)
    {
        $temporaryFile = FileProcessor::TMP_DIR . '/' . ltrim($value['file'], '/');

        if ($this->getFileProcessor()->isExist($temporaryFile)) {
            $base64EncodedData = $this->getFileProcessor()->getBase64EncodedData($temporaryFile);

            /** @var ImageContentInterface $imageContentDataObject */
            $imageContentDataObject = $this->getImageContentFactory()->create()
                ->setName($value['name'])
                ->setBase64EncodedData($base64EncodedData)
                ->setType($value['type']);

            // Remove temporary file
            $this->getFileProcessor()->removeUploadedFile($temporaryFile);

            return $imageContentDataObject;
        }

        return $this->_value;
    }

    /**
     * Get ImageContentInterfaceFactory instance
     *
     * @return ImageContentInterfaceFactory
     *
     * @deprecated
     */
    private function getImageContentFactory()
    {
        if ($this->imageContentFactory === null) {
            $this->imageContentFactory = ObjectManager::getInstance()
                ->get(\Magento\Framework\Api\Data\ImageContentInterfaceFactory::class);
        }
        return $this->imageContentFactory;
    }
}
